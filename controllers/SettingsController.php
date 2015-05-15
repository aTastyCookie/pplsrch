<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

use app\models\SettingsForm;
use app\models\User;

class SettingsController extends Controller
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
    	$userAuth = Yii::$app->user->getIdentity();
    	$this->view->params['user'] = $userAuth;

        $user = User::findIdentity($userAuth->getId());
    	
    	$settingsModel = new SettingsForm();
    	$settingsModel->clientMaxResults = $user->getSettingsResultsMax();

    	if ($settingsModel->load(Yii::$app->request->post()) && $settingsModel->validate()) {
    		$user->setSettingsResultsMax($settingsModel->clientMaxResults);
    		$user->save();

    		return $this->render('index', [
    			'settingsModel' => $settingsModel,
    		]);
    	}

    	return $this->render('index', [
    		'settingsModel' => $settingsModel,
    	]);
    }
}