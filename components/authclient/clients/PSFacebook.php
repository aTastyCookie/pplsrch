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

    protected function getNormalizeSearchResultMap()
    {
        return [
            'picture' => function($data) {
                return $data['picture']['data']['url'];
            },
            'alternate_name' => function() {
                return NULL;
            },
            'mobile_phone' => function() {
                return NULL;
            },
            'home_phone' => function() {
                return NULL;
            },
            'profile_url' => function($data) {
                return 'https://www.facebook.com/profile.php?id=' . $data['id'];
            }
        ];
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

    protected function normalizeSearchResult($data) 
    {
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
        $search = $this->api('/search', 'GET', ['q' => $query, 'type' => 'user', 'limit' => 50]);

        $results = array();
        foreach ($search['data'] as $profile) {
            $profilesData[] = $this->getProfileData($profile['id']); 
        } 
        
        $result = $this->normalizeSearchResult($profilesData);        
 
        return $result;
    }

    public function getProfileData($userId)
    {
        $data = $this->api('/' . $userId, 'GET', ['fields' => 'email,name,link,picture']);

        $pictureBig = $this->api('/' . $userId . '/picture?type=large&redirect=false', 'GET');
        $data['picture_big'] = $pictureBig['data']['url'];

        return $data;
    }
}
