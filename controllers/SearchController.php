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
                        'actions' => ['index', 'vk', 'connect', 'auth', 'disconnect'],
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

        $token = $client->getAccessToken()->getToken();
        $tokenSecret = $client->getAccessToken()->getTokenSecret();
        $attributes = $client->getUserAttributes();

        $authExists = Auth::find()->where([
            'source' => $client->getId(),
            'user_id' => $user->id
        ])->one();

        if ($authExists) {
            $authExists->connect();
        } else {
            $auth = new Auth([
                'user_id' => $user->id,
                'source' => $client->getId(),
                'source_id' => (string)$attributes['id'],
                'access_token' => $token,
                'access_token_secret' => $tokenSecret
            ]);

            $auth->save();    
        }
    }

	public function actionIndex()
    {
        $user = Yii::$app->user->getIdentity();
        $request = Yii::$app->request;
        $form = new SearchForm();

        $connectedClients = $user->getConnectedClients();
        
        if ($connectedClients) {
            $connectedClientIds = array();
            foreach ($connectedClients as $client) {
                $connectedClientIds[] = $client->getSource();             
            }
        }

        if ($request->isPost) {
            
            

            $q = urldecode($request->post('q'));
            
            if ($connectedClients) {
                $results = $this->search($connectedClients, $q);
            }

            if (Yii::$app->request->isAjax) {
                
                Yii::$app->response->format = Response::FORMAT_HTML;

                if (count($results)) {
                    $html = '';
                    foreach ($results as $client => $profiles) {
                        $html .= '<div class="client-name">Результаты поиска ' . $client . ':</div>';
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
                    }
                }

                return $html;

            } else {

                return $this->render('index', [
                    'formModel' => $form,
                    'user' => $user,
                    'results' => $results,
                    'connectedClients' => isset($connectedClientIds) ? $connectedClientIds : NULL,
                ]);
            }

        }

        return $this->render('index', [
            'formModel' => $form,
            'user' => $user,
            'connectedClients' => isset($connectedClientIds) ? $connectedClientIds : NULL,
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

    public function search($clients, $query)
    {
        $results = array();
        foreach ($clients as $client) {
            if ($client->getSource() == 'linkedin') continue;

            $results[$client->getSource()] = $this->searchInsideClient($client, $query);
        }     
        
        return $results;
    }

    public function searchInsideClient($client, $query)
    {
        switch ($client->getSource()) {
            case 'vkontakte':
                $clientOAuth = new PSVKontakte();
                break;

            case 'facebook':
                $clientOAuth = new PSFacebook();
                break;

            case 'google':
                $clientOAuth = new PSGoogleOAuth();
                break;

            case 'twitter':
                $clientOAuth = new PSTwitter();
                break;

            case 'linkedin':
                $clientOAuth = new LinkedIn();
                break;
        }

        if (is_object($clientOAuth)) {
            $token = new OAuthToken();
            $token->setToken($client->access_token);
            if ($client->getSource() == 'twitter') {
                $token->setTokenSecret($client->access_token_secret);
            }
            $clientOAuth->setAccessToken($token);

            if ($client->getSource() == 'twitter') {
                $clientOAuth->consumerKey = Yii::$app->components['authClientCollection']['clients']['twitter']['consumerKey'];
                $clientOAuth->consumerSecret = Yii::$app->components['authClientCollection']['clients']['twitter']['consumerSecret'];
            }


            $result = $clientOAuth->searchUsers($query);
        } 

        return $result;
    }


    private function _indexByKey($array, $key) {
        $indexedAr = array();
        foreach ($array as $el) {
            $indexedAr[$el[$key]] = $el;
            unset($indexedAr[$el[$key]][$key]);
        }

        return $indexedAr;
    }
}