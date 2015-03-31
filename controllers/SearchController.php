<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\SearchForm;
use yii\authclient\clients\VKontakte;
use yii\authclient\clients\Facebook;
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
                        'actions' => ['index', 'vk'],
                        'roles' => ['@'],
                    ],
                ]
            ],
        ];
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