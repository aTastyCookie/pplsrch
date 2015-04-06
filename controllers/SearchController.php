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

    public function actionVk()
    {
        $request = Yii::$app->request;
        $post = $request->post();
        $q = $post['q'];

        $user = Yii::$app->user->getIdentity();

        //$vk = new VKontakte();

        //$res = $vk->api('users.search', 'GET', ['q' => $q, 'fields' => 'contacts, photo_50']);
        //var_dump($res);

        $fb = new Facebook();

        $token = new OAuthToken();
        $token->setToken($user->access_token);

        $fb->setAccessToken($token);

        $res = $fb->api('search/?q=' . $q . '&type=user', 'GET');
        var_dump($res);

        /*$request = Yii::$app->request;
        $post = $request->post();*/

        //$client = new VKontakte();

        //var_dump($post);
    }
}