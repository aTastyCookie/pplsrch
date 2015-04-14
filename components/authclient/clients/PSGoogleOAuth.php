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

    protected function getNormalizeSearchResultMap()
    {
        return [
            'name' => 'displayName',
            'picture' => function($attributes) {
                return $attributes['image']['url'];
            }
        ];
    }

    public function searchUsers($query)
    {
        $data = $this->api('people', 'GET', ['query' => $query, 'maxResults' => '20']);

        $result = $this->normalizeSearchResult($data['items']);

        return $result;
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

}