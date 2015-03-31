<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\Facebook;

class PSFacebook extends Facebook
{
    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $userAttributes = $this->api('me', 'GET');

        $pictureRes = $this->api('me/picture?redirect=false', 'GET');
        $userAttributes['photo'] = $pictureRes['data']['url'];
        
        return $userAttributes; 
    }

    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'name' => function ($attributes) {
                return $attributes['first_name'] . ' ' . $attributes['last_name'];
            }
        ];
    }
}
