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

    public function searchUsers($query)
    { 
        $result = $this->api('users/search.json', 'GET', ['q' => $query]);

        return $result;
    }
}
