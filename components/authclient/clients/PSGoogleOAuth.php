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

    public function searchUsers($query)
    {
        $result = $this->api('plus/v1/people', 'GET', ['query' => $query, 'maxResults' => 20]);

        return $result;
    }

}