<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\LinkedIn;

class PSLinkedIn extends LinkedIn
{
    /**
     * @inheritdoc
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'name' => function ($attributes) {
            	return $attributes['first-name'] . ' ' . $attributes['last-name'];
            },
            'photo' => 'picture-url'
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initUserAttributes()
    {
        $attributeNames = [
            'id',
            'email-address',
            'first-name',
            'last-name',
            'public-profile-url',
            'picture-url',
        ];

        return $this->api('people/~:(' . implode(',', $attributeNames) . ')', 'GET');
    }
}