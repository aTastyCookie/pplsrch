<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\authclient\OAuthToken;

class Auth extends ActiveRecord 
{
    const DISCONNECTED = 0;
    const CONNECTED = 1;

    public function getUser()
    {
        $user = User::find()->where([
        	'id' => $this->user_id
        ])->one();

        return $user;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getAuthToken()
    {
        $tokenParams = [
            'token' => $this->access_token,
            'tokenSecret' => $this->access_token_secret,
            'createTimestamp' => $this->token_create_timestamp,
            'expireDuration' => $this->token_expires_in
        ];

        $token = new OAuthToken($tokenParams);

        return $token;
    }

    public function setAuthToken(OAuthToken $token)
    {
        $this->access_token = $token->getToken();
        $this->token_create_timestamp = $token->createTimestamp;
        $this->access_token_secret = $token->getTokenSecret();
        $this->token_expires_in = $token->getExpireDuration();
        $this->save();
    }

    public function disconnect()
    {
        $this->status = self::DISCONNECTED;
        $this->save();

        return TRUE;
    }

    public function enable()
    {
        $this->status = self::CONNECTED;
        $this->save();
    
        return TRUE;
    }

    public function refreshConnect()
    {
        $token = $this->getAuthToken();
        var_dump($token);die();
    }

}