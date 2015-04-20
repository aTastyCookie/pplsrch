<?php

namespace app\components\authclient;

use yii\authclient\AuthAction;

class PSAuthAction extends AuthAction
{
    public function auth($client)
    {
        if ($client instanceof OpenId) {
            return $this->authOpenId($client);
        } elseif ($client instanceof OAuth2) {
            return $this->authOAuth2($client);
        } elseif ($client instanceof OAuth1) {
            return $this->authOAuth1($client);
        } else {
            throw new NotSupportedException('Provider "' . get_class($client) . '" is not supported.');
        }
    }
}