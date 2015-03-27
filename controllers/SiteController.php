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
        ];
    }

    public function onAuthSuccess($client)
    {

        $attributes = $client->getUserAttributes();

        /** @var Auth $auth */
        $auth = User::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if ($auth) {
            Yii::$app->user->login($auth);
        } else {
            $user = new User([
                'source' => $client->getId(),
                'source_id' => (string)$attributes['id']
            ]);
            if ($user->save()) {
                Yii::$app->user->login($user);
            } else {
                print_r($auth->getErrors());
            }
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
    
	public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}