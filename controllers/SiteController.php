<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Auth;
use app\models\User;

class SiteController extends Controller
{
	public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
            'authreg' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthRegSuccess'],
            ]
        ];
    }

    public function onAuthRegSuccess($client) {

        $token = $client->getAccessToken()->getToken();
        $attributes = $client->getUserAttributes();

        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if ($auth) {
            return $this->redirect(['site/index', 'reg' => '1']);
        } else {
            $user = new User([
                'photo' => isset($attributes['photo']) ? $attributes['photo'] : NULL,
                'name' => $attributes['name'] ? $attributes['name'] : NULL,
                'email' => $attributes['email'],
            ]);
            $transaction = $user->getDb()->beginTransaction();
            if ($user->save()) {
                $auth = new Auth([
                    'user_id' => $user->id,
                    'source' => $client->getId(),
                    'source_id' => (string)$attributes['id'],
                    'access_token' => $token,
                ]);
                if ($auth->save()) {
                    $transaction->commit();
                    Yii::$app->user->login($user);
                } else {
                    print_r($auth->getErrors());
                }
            } else {
                print_r($auth->getErrors());
            }
        }
    }

    public function onAuthSuccess($client)
    {
        $attributes = $client->getUserAttributes();
        
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if ($auth) {
            $user = $auth->getUser();
            $token = $client->getAccessToken();
            $auth->setAuthToken($token);
            Yii::$app->user->login($user);
        } else {
            return $this->redirect(['site/index', 'need_reg' => '1']);
        }
    }

    public function actionLogin()
    {
        $client = Yii::$app->request->get('client');
        $error = Yii::$app->request->get('error');
        if ($error) {
            var_dump('Нет учетной записи $client');die();
        }
    }
    
	public function actionIndex()
    {
        $user = Yii::$app->user;
        if (!$user->isGuest) {
            return $this->redirect(['search/index']);
        }
        $registered = FALSE;
        $needRegistration = FALSE;

        $get = Yii::$app->request->get();
        if (isset($get['reg'])) {
            $registered = TRUE;
        }
        if (isset($get['need_reg'])) {
            $needRegistration = TRUE;
        }

        return $this->render('index', [
            'registered' => $registered,
            'needRegistration' => $needRegistration
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}