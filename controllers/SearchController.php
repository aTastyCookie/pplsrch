<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\models\SearchForm;
use app\components\authclient\clients\PSVKontakte;
use app\components\authclient\clients\PSFacebook;
use app\components\authclient\clients\PSTwitter;
use app\components\authclient\clients\PSGoogleOAuth;
use yii\authclient\OAuthToken;
use app\models\Auth;

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
                        'actions' => ['index', 'vk', 'connect', 'auth', 'disconnect', 'search-profiles'],
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
                            $html .= '<div class="profile">
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
        
        $request = Yii::$app->getRequest();

        $limit = 20;
        $clientId = $request->post('client');
        $offset = $request->post('offset');
        $queryString = $request->post('q');
        $after = $request->post('after');                 
        
        $collection = Yii::$app->get('authClientCollection');
        
        if ($collection->hasClient($clientId)) {
            $client = $collection->getClient($clientId);
            $searchResult = $client->searchUsers($queryString, $offset, $limit, $after);

            $html = '';
            if ($searchResult->getProfiles()) {
                $html .= $this->getFoundProfilesHtml($searchResult->getProfiles());
            }
            //var_dump($searchResult->canGetMore());die();
            if ($searchResult->canGetMore()) {
                $moreLink = '<button type="button" class="get-more"';
                if ($searchResult->getAfter()) {
                   $moreLink .= ' data-after="' . $searchResult->getAfter() . '"'; 
                }
                $moreLink .= ' data-client="' . $clientId . '" data-offset="' . $searchResult->getOffset() . '" data-query="' . $queryString . '">GiveMeMore</button>';
            }
        }

        return [
            'profiles' => $html,
            'more' => isset($moreLink) ? $moreLink : NULL
        ];
    }

    public function getFoundProfilesHtml($profiles) {
        if (!count($profiles)) {
            return false;
        }

        $html = '';
        foreach ($profiles as $profile) {
            $html .= '<div class="profile">
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
}