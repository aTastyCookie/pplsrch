<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\GoogleOAuth;

class PSGoogleOAuth extends GoogleOAuth
{
    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'name' => 'displayName',
            'photo' => function($attributes) {
                return $attributes['image']['url'];
            }
        ];
    }

}