<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\Facebook;
use app\components\SearchResult;

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

    public function searchUsers($queryString, $offset = 0, $limit = 20, $after)
    {
        $search = $this->api('/search', 'GET', ['q' => $queryString, 'type' => 'user', 'limit' => 5000]);
        


        $totalProfilesCount = count($search['data']);

        $profilesList = array_slice($search['data'], $offset);
        
        $queryItemsCount = count($profilesList);
        if ($queryItemsCount > $limit) {
            $profilesList = array_slice($profilesList, 0, $limit);
            $queryItemsCount = $limit;
        }

        $profilesData = array();
        foreach ($profilesList as $profileInfo) {
            $profilesData[] = $this->getProfileData($profileInfo['id']); 
        } 
        
        $result = $this->normalizeSearchResult($profilesData);        
 
        $searchResult = new SearchResult($this->getId(), $result);

        if (($offset + $queryItemsCount) < $totalProfilesCount) {
            $nextQueryOffset = $offset + $limit;
            $searchResult->setOffset($nextQueryOffset);
        } else {
            $searchResult->setOffset(FALSE);
        }

        return $searchResult;
    }

    public function getProfileData($userId)
    {
        //$data = $this->api('/' . $userId, 'GET', ['fields' => 'email,name,link,picture']);
        $data = $this->api('/' . $userId, 'GET', ['fields' => 'email,name,link,picture,about,address,age_range,bio,birthday,devices,education,gender']);

        $pictureBig = $this->api('/' . $userId . '/picture?type=large&redirect=false', 'GET');
        $data['picture_big'] = $pictureBig['data']['url'];

        return $data;
    }
}
