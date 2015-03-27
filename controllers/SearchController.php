<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

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
                        'actions' => ['index'],
                        'roles' => ['@'],
                    ],
                ]
            ],
        ];
    }

	public function actionIndex()
	{
		return $this->render('index');
	}
}