<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\VKontakte;

class PSVKontakte extends VKontakte
{
    protected function getNormalizeSearchResultMap()
    {
        return [
            'id' => 'uid',
            'name' => function ($data) {
                return $data['first_name'] . ' ' . $data['last_name'];
            }
        ];
    }

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

    protected function normalizeSearchResult($data) 
    {
        unset($data[0]);
        foreach ($data as &$profile) {
            foreach ($this->getNormalizeSearchResultMap() as $normalizedName => $actualName) {
                if (is_scalar($actualName)) {
                    if (array_key_exists($actualName, $profile)) {
                        $profile[$normalizedName] = $profile[$actualName];
                    }
                } else {
                    if (is_callable($actualName)) {
                        $profile[$normalizedName] = call_user_func($actualName, $profile);
                    } elseif (is_array($actualName)) {
                        $haystack = $profile;
                        $searchKeys = $actualName;
                        $isFound = true;
                        while (($key = array_shift($searchKeys)) !== null) {
                            if (is_array($haystack) && array_key_exists($key, $haystack)) {
                                $haystack = $haystack[$key];
                            } else {
                                $isFound = false;
                                break;
                            }
                        }
                        if ($isFound) {
                            $profile[$normalizedName] = $haystack;
                        }
                    } else {
                        throw new InvalidConfigException('Invalid actual name "' . gettype($actualName) . '" specified at "' . get_class($this) . '::normalizeUserAttributeMap"');
                    }
                }
            }
        }

        return $data;
    }

    public function searchUsers($query)
    {
        $data = $this->api('users.search', 'GET', ['q' => $query, 'fields' => 'contacts, photo_50', 'count' => 1000]);
        
        $result = $this->normalizeSearchResult($data['response']);
        var_dump($result);die();

        return $result;
    }
}