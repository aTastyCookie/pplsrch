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

    public function searchUsers($query)
    {
        $data = $this->api('/search', 'GET', ['q' => $query, 'type' => 'user', 'limit' => 20]);

        $result = array();
        foreach ($data['data'] as $profile) {
            $result[] = $this->getProfileData($profile['id']); 
        } 

        return $result;
    }

    public function getProfileData($userId)
    {
        $data = $this->api('/' . $userId, 'GET');

        $picture = $this->api('/' . $userId . '/picture?redirect=false', 'GET');
        $data['picture'] = $picture['data']['url'];

        return $data;
    }
}
