<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

use app\models\SearchForm;
use app\models\SearchRequest;
use app\models\Auth;

use app\components\authclient\clients\PSVKontakte;
use app\components\authclient\clients\PSFacebook;
use app\components\authclient\clients\PSTwitter;
use app\components\authclient\clients\PSGoogleOAuth;

use yii\authclient\OAuthToken;


class SearchController extends Controller
{
    public $layout = 'search';
	public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'vk', 'connect', 'auth', 'disconnect', 'search-profiles', 'compare-pics'],
                        'roles' => ['@'],
                    ],
                ]
            ],
        ];
    }

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccessConnect'],
            ],
        ];
    }

    public function actionComparePics()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->isPost) {
            $imagesStr = $request->post('images');
            $hashesStr = $request->post('hashes');

            $images = explode(',', $imagesStr);
            $hashes = explode(',', $hashesStr);

            $picsData = array();
            foreach ($images as $key => $src) {
                $picsData[] = array(
                    'src' => $src,
                    'hash' => $hashes[$key],
                );
            }

            $groups = array();
            foreach ($picsData as $key => $data) {
                $similarPicsIndexes = $this->getSimilarPicsIndexes($key, $data['src'], $picsData);
                if (count($similarPicsIndexes)) {
                    foreach ($similarPicsIndexes as $index) {
                        $groups[$data['hash']][] = $picsData[$index]['hash'];
                        unset($picsData[$index]);
                    }
                }
                unset($picsData[$key]);
            }

            return $groups;
        }
    }

    public function getSimilarPicsIndexes($index, $src, $picsData)
    {
        $srcImageKey = $this->generateImageKey($src);
        
        if (!$srcImageKey) {
            return FALSE;
        }

        //$result = array();
        $similarPicsIndexes = array();
        foreach ($picsData as $key => $data) {
            if ($key > $index) {
                $imageKey = $this->generateImageKey($data['src']);
                if (!$imageKey) continue;    
                $similarity = $this->imagediff($srcImageKey, $imageKey);
                
                if ($similarity == 1) {
                    $similarPicsIndexes[] = $key;
                }
            }
        }

        return $similarPicsIndexes;
    }

    public function onAuthSuccessConnect($client)
    {
        $user = Yii::$app->user->getIdentity();
        $authToken = $client->getAccessToken();

        $authExists = Auth::find()->where([
            'source' => $client->getId(),
            'user_id' => $user->id
        ])->one();

        if ($authExists) {
            $auth = $authExists;
            $auth->status = 1;
        } else {
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => $client->getId(),
                'source_id' => (string)$attributes['id'],
                'status' => 1
            ]);    
        }

        $auth->setAuthToken($authToken);
        $auth->save();
    }

	public function actionIndex()
    {
        $user = Yii::$app->user->getIdentity();
        $this->view->params['user'] = $user;

        $request = Yii::$app->request;
        $form = new SearchForm();

        $connectedAuths = $user->getConnectedAuths();
        
        if ($connectedAuths) {
            $connectedAuths = $this->disableExpiredAuths($connectedAuths);
        }
    
        if ($request->isPost) {
            
            $q = urldecode($request->post('q'));
            
            if ($connectedAuths) {
                $results = $this->search($connectedAuths, $q);
            }

            if (Yii::$app->request->isAjax) {
                
                Yii::$app->response->format = Response::FORMAT_HTML;

                if (count($results)) {
                    $html = '';
                    foreach ($results as $result) {
                        $html .= '<div class="client-name">Результаты поиска ' . $result->getClientId() . ':</div>';
                        
                        foreach ($result->getProfiles() as $profile) {
                            $html .= '<div id="' . md5($profile['picture']) . '" class="profile">
                                <div class="top-profile">
                                    <a class="show-more">развернуть</a>
                                    <div class="picture">
                                        <img src="' . $profile['picture'] . '" />
                                    </div>
                                    <div class="name">' . $profile['name'] . '</div>
                                </div>
                                <div class="profile-more-data">
                                    <div class="picture-big">
                                        <img src="' . $profile['picture_big'] . '" />
                                    </div>
                                    <div class="contact-info">
                                        <div class="name">' . $profile['name'] . '</div>'
                                        . ($profile['mobile_phone'] ? '<div class="mobile-phone">' . $profile['mobile_phone'] . '</div>' : '') 
                                        . ($profile['home_phone'] ? '<div class="home-phone">' . $profile['home_phone'] . '</div>' : '') .
                                        '<a href="' . $profile['profile_url'] . '">' . $profile['profile_url'] . '</a>
                                    </div>
                                </div>
                            </div>';
                            $html .= '<br>' . '<pre>' . print_r($profile, true) . '</pre>';
                        }
                    }
                }

                return $html;

            } else {

                return $this->render('index', [
                    'formModel' => $form,
                    'user' => $user,
                    'results' => $results,
                    'connectedClients' => isset($connectedAuths) ? $connectedAuths : NULL,
                ]);
            }

        }

        return $this->render('index', [
            'formModel' => $form,
            'user' => $user,
            'connectedClients' => isset($connectedAuths) ? $connectedAuths : NULL,
        ]);
    }

    public function actionConnect()
    {
        $user = Yii::$app->user->getIdentity();

        $auths = Auth::find()->where([
            'user_id' => $user->id,
        ])->asArray()->all();

        $auths = $this->_indexByKey($auths, 'source');

        return $this->render('connect', [
            'user' => $user,
            'auths' => $auths,
        ]);
    }

    public function actionDisconnect()
    {
        $user = Yii::$app->user->getIdentity();
        $authClientId = Yii::$app->request->get('authclient');

        $auth = Auth::find()->where([
            'user_id' => $user->id,
            'source' => $authClientId
        ])->one();

        if ($auth->disconnect()) {
            return $this->redirect(['connect']);
        }
    }

    public function search($auths, $query)
    {
        $collection = Yii::$app->get('authClientCollection');
        
        $results = array();
        foreach ($auths as $auth) {
            $clientId = $auth->getSource();

            if ($clientId == 'linkedin') continue;
            
            if ($collection->hasClient($clientId)) {

                $client = $collection->getClient($clientId);
                
                $authToken = $auth->getAuthToken();
                if ($authToken->getIsExpired()) continue;
                $client->setAccessToken($authToken);

                $this->runAction('search-profiles', ['clientId' => $clientId, 'queryString' => $query]);

                //$results[] = $this->searchInsideClient($client, $query);
            }
        }     
        
        return $results;
    }

    public function searchInsideClient($client, $query)
    {
        if (is_object($client)) {

            if ($client->getId() == 'twitter') {
                $client->consumerKey = Yii::$app->components['authClientCollection']['clients']['twitter']['consumerKey'];
                $client->consumerSecret = Yii::$app->components['authClientCollection']['clients']['twitter']['consumerSecret'];
            }


            $searchResult = $client->searchUsers($query);
            //var_dump($searchResult);die();
        } 

        return $searchResult;
    }

    public function actionSearchProfiles()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->getIdentity(); 

        $request = Yii::$app->getRequest();

        $limit = 20;
        $clientId = $request->post('client');
        $offset = $request->post('offset');
        $queryString = $request->post('q');
        $after = $request->post('after');                 
        
        $collection = Yii::$app->get('authClientCollection');
        
        if ($collection->hasClient($clientId)) {           
            $auth = Auth::find()->where([
                'source' => $clientId,
                'user_id' => $user->getId()
            ])->one();

            $accessToken = $auth->getAuthToken();

            if ($accessToken->getIsValid()) {
                $client = $collection->getClient($clientId);
                $client->setAccessToken($accessToken);    
                $searchResult = $client->searchUsers($queryString, $offset, $limit, $after);

                $profilesData = $searchResult->getProfiles();

                //var_dump($profilesData[0]['picture']);
                //$this->generateImageKey($profilesData[0]['picture']);
                //var_dump(getimagesize($profilesData[0]['picture']));die();


                if ($profilesData) {
                    $html = $this->getFoundProfilesHtml($profilesData);
                }
                
                if ($searchResult->canGetMore()) {
                    $moreLink = '<button type="button" class="get-more"';
                    if ($searchResult->getAfter()) {
                       $moreLink .= ' data-after="' . $searchResult->getAfter() . '"'; 
                    }
                    $moreLink .= ' data-client="' . $clientId . '" data-offset="' . $searchResult->getOffset() . '" data-query="' . $queryString . '">GiveMeMore</button>';
                }

                $searchRequestLog = new SearchRequest([
                    'user_id' => $user->getId(),
                    'ip' => $request->userIP,
                    'ua' => $request->userAgent,
                    'query' => $queryString,
                    'result' => serialize($profilesData),
                    'search_time' => date('Y-m-d H:i:s')
                ]);

                $searchRequestLog->save();

                return [
                    'profiles' => isset($html) ? $html : '<p class="error">Поиск не дал результатов</p>',
                    'more' => isset($moreLink) ? $moreLink : NULL
                ];

            } else {
                return [
                    'error' => '<p class="error">Ошибка токена. Необходимо обновить</p>'
                ];
            }             
        } 
    }

    public function getFoundProfilesHtml($profiles) 
    {
        $html = '';
        $profiles[] = array(
            'picture' => 'http://cs319821.vk.me/v319821463/6a66/QxyKYWJSWSo.jpg',
            'name' => 'Виталий Онанко',
            'picture_big' => 'http://cs319821.vk.me/v319821463/6a66/QxyKYWJSWSo.jpg',
            'mobile_phone' => NULL,
            'home_phone' => NULL,
            'profile_url' => 'sdsdfsd',
        );
        foreach ($profiles as $key => $profile) {
            $html .= '<div id="' . md5($profile['picture'] . $key) . '" class="profile">
                <div class="top-profile">
                    <a class="show-more">развернуть</a>
                    <div class="picture">
                        <img src="' . $profile['picture'] . '" />
                    </div>
                    <div class="name">' . $profile['name'] . '</div>
                </div>
                <div class="profile-more-data">
                    <div class="picture-big">
                        <img src="' . $profile['picture_big'] . '" />
                    </div>
                    <div class="contact-info">
                        <div class="name">' . $profile['name'] . '</div>'
                        . ($profile['mobile_phone'] ? '<div class="mobile-phone">' . $profile['mobile_phone'] . '</div>' : '') 
                        . ($profile['home_phone'] ? '<div class="home-phone">' . $profile['home_phone'] . '</div>' : '') .
                        '<a href="' . $profile['profile_url'] . '">' . $profile['profile_url'] . '</a>
                    </div>
                </div>
            </div>';
        }

        return $html;
    }


    private function _indexByKey($array, $key) {
        $indexedAr = array();
        foreach ($array as $el) {
            $indexedAr[$el[$key]] = $el;
            unset($indexedAr[$el[$key]][$key]);
        }

        return $indexedAr;
    }

    public function disableExpiredAuths($auths)
    {
        foreach ($auths as $key => $auth) {
            $authToken = $auth->getAuthToken();
            if ($authToken->getIsExpired()) {
                $auth->status = 0;
                $auth->save();
                unset($auths[$key]);
            }
        }

        return $auths;
    }

    public function generateImageKey($src)
    {
        $size = @getimagesize($src);
        
        if (!$size) {
            return FALSE;
        }

        preg_match('#jpeg|png|jpg|gif#', $src, $match);
        
        switch ($match[0]) {
            case 'jpeg':
                $image = @imagecreatefromjpeg($src);
                break;

            case 'jpg':
                $image = @imagecreatefromjpeg($src);
                break;

            case 'png':
                $image = @imagecreatefrompng($src);
                break;

            case 'gif':
                $image = @imagecreatefromgif($src);
                break;

            default:
                $image = FALSE;
                break;            
        }

        if (!$image) return FALSE;

        $zone = imagecreate(20, 20);
        imagecopyresized($zone, $image, 0, 0, 0, 0, 20, 20, $size[0], $size[1]);

        //Будущая маска
        $colormap = array();

        //Базовый цвет изображения
        $average = 0;

        //Результат
        $result = array();

        for ($x = 0; $x < 20; $x++) {
            for ($y = 0; $y < 20; $y++) {
                $color = imagecolorat($zone, $x, $y);
                $color = imagecolorsforindex($zone, $color);

                //Вычисление яркости было подсказано хабраюзером Ryotsuke
                $colormap[$x][$y]= 0.212671 * $color['red'] + 0.715160 * $color['green'] + 0.072169 * $color['blue'];
                $average += $colormap[$x][$y];
            }
        }

        //Базовый цвет
        $average /= 400;

        //Генерируем ключ строку
        for ($x = 0; $x < 20; $x++)
            for ($y = 0; $y < 20; $y++)
                $result[] = ($x < 10 ? $x : chr($x + 97)) . ($y < 10 ? $y : chr($y + 97)) . round(2*$colormap[$x][$y]/$average);

        //Возвращаем ключ
        return join(' ',$result);
    }

    public function imagediff($image, $desc)
    {
        $image = explode(' ', $image);
        $desc = explode(' ', $desc);

        $result = 0;

        foreach ($image as $bit) {
            if(in_array($bit,$desc)) {
                $result++;
            }
        }

        return $result/((count($image) + count($desc))/2);
    }
}