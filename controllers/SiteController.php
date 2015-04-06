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
        $token = $client->getAccessToken()->getToken();

        $attributes = $client->getUserAttributes();

        //var_dump($attributes);die;

        //var_dump($attributes);die;

        //var_dump($attributes);die();

        /** @var Auth $auth */
        
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if ($auth) {
            $user = $auth->getUser();
            Yii::$app->user->login($user);
        } else {
            return $this->redirect(['site/index', 'need_reg' => '1']);
        }


        

        //var_dump($auth->getUser());die;

        //$user = $auth->getUser($auth->id);
        
        /*if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->getUser();
                Yii::$app->user->login($user);
            } else { // signup


                if (isset($attributes['username']) && User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $attributes['nickname'],
                        //'email' => $attributes['email'],
                        'password_hash' => $password,
                    ]);

                    
                    //$user->generateAuthKey();
                    //$user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        //var_dump($user);die();
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $client->getId(),
                    'source_id' => $attributes['id'],
                ]);
                $auth->save();
            }
        }*/
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