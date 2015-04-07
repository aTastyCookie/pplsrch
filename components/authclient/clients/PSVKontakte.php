<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\VKontakte;

class PSVKontakte extends VKontakte
{
    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'uid',
            'name' => function ($attributes) {
            	return $attributes['first_name'] . ' ' . $attributes['last_name'];
            }
        ];
    }

    public function searchUsers($query)
    {
        $result = $this->api('users.search', 'GET', ['q' => $query, 'fields' => 'contacts, photo_50', 'count' => 1000]);

        return $result;
    }
}