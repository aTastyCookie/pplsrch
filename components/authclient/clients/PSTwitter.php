<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\Twitter;

class PSTwitter extends Twitter
{
    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'photo' => 'profile_image_url'
        ];
    }
}
