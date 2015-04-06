<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\SearchForm;
use yii\authclient\clients\VKontakte;
use yii\authclient\clients\Facebook;
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
                        'actions' => ['index', 'vk', 'connect', 'auth'],
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
        $attributes = $client->getUserAttributes();

        $auth = new Auth([
            'user_id' => $user->id,
            'source' => $client->getId(),
            'source_id' => (string)$attributes['id'],
            'access_token' => $token,
        ]);

        $auth->save();        
    }

	public function actionIndex()
	{
        $user = Yii::$app->user->getIdentity();
        $request = Yii::$app->request;
        if ($request->isPost) {
            $q = $request->post('q');

            $connectedClients = $user->getConnectedClients();
            
            if ($connectedClients) {
                $result = $this->search($connectedClients, $q);
            }


            $fb = new Facebook();

            
            //$this->searchFb($q);
        }

        $form = new SearchForm();

		return $this->render('index', [
            'formModel' => $form,
            'user' => $user
        ]);
	}

    public function actionConnect()
    {
        $user = Yii::$app->user->getIdentity();

        $auths = Auth::find()->where([
            'user_id' => $user->id,
        ])->all();

        foreach ($auths as $auth) {
            $socials[$auth->source] = $auth;
        }



        return $this->render('connect', [
            'user' => $user,
            'socials' => $socials,
        ]);
    }

    public function actionConnectAccount()
    {

    }

    public function search($clients, $query)
    {
        $results = array();
        foreach ($clients as $client) {
            $results = array_merge($results, $this->searchInsideClient($client, $query));
        }      
        die();
    }

    public function searchInsideClient($client, $query)
    {
        switch ($client->getSource()) {
            case 'vkontakte':
                $clientOAuth = new VKontakte();
                break;

            case 'facebook':
                $clientOAuth = new Facebook();
                break;

            case 'google':
                $clientOAuth = new GoogleOAuth();
                break;

            case 'twitter':
                $clientOAuth = new Twitter();
                break;

            case 'linkedin':
                $clientOAuth = new LinkedIn();
                break;
        }

        if (is_object($clientOAuth)) {
            $token = new OAuthToken();
            $token->setToken($client->access_token);
            $clientOAuth->setAccessToken($token);
            //$res = $clientOAuth->api('search/?q=' . $query . '&type=user', 'GET');
            $res = $clientOAuth->api('search/', 'GET', ['q' => $query, 'type' => 'user']);
            var_dump($res);
        } 

        return array();
    }

    public function searchFb($q)
    {

        $user = Yii::$app->user->getIdentity();
        $auth = Auth::find()->where([
            'source' => 'facebook',
            'user_id' => $user->id,
        ])->one();

        /*$vk = new VKontakte();

        $token = new OAuthToken();
        $token->setToken($auth->access_token);

        $vk->setAccessToken($token);

        $res = $vk->api('users.search', 'GET', ['q' => $q, 'fields' => 'contacts, photo_50']);
        var_dump($res);*/

        $fb = new Facebook();

        $token = new OAuthToken();
        $token->setToken($auth->access_token);

        $fb->setAccessToken($token);

        $res = $fb->api('search/?q=' . $q . '&type=user', 'GET');
        var_dump($res);die();

        /*$request = Yii::$app->request;
        $post = $request->post();*/

        //$client = new VKontakte();

        //var_dump($post);
    }
}