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

    protected function getNormalizeSearchResultMap()
    {
        return [
            'picture' => 'profile_image_url'
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
        $data = $this->api('users/search.json', 'GET', ['q' => $query]);

        $result = $this->normalizeSearchResult($data);

        return $result;
    }
}
