<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\SearchForm;
use yii\authclient\clients\VKontakte;

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

        $vk = new VKontakte();

        $wall = $vk->api('wall.get', 'GET');
        var_dump($wall);

        /*$request = Yii::$app->request;
        $post = $request->post();*/

        //$client = new VKontakte();

        //var_dump($post);
    }
}