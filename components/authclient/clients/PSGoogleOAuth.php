<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\GoogleOAuth;
use app\components\SearchResult;

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
            'picture_small' => function($profile) {
                return $profile['image']['url'];
            },
            'default_picture' => function($profile) {
                return $profile['image']['isDefault'];
            },
            'picture_big' => function($profile) {
                return preg_replace('|sz=50|', 'sz=200', $profile['image']['url']);
            },
            'profile_url' => 'url',
            'mobile_phone' => function() {
                return NULL;
            },
            'home_phone' => function() {
                return NULL;
            }
        ];
    }

    public function searchUsers($queryString, $offset, $limit, $after)
    {
        $queryParams = [
            'query' => $queryString,
            'maxResults' => $limit
        ];

        if ($offset) {
            $queryParams['pageToken'] = $offset;
        }

        $search = $this->api('people', 'GET', $queryParams);
        $queryItemsCount = count($search['items']);

        $profilesData = array();
        foreach ($search['items'] as $profile) {
            $profilesData[] = $this->getProfileData($profile['id']); 
        }

        $result = $this->normalizeSearchResult($profilesData);

        $searchResult = new SearchResult($this->getId(), $result);

        if ($queryItemsCount == $limit) {
            $searchResult->setOffset($search['nextPageToken']);
        } else {
            $searchResult->setOffset(FALSE);
        }

        return $searchResult;
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

    public function getProfileData($userId)
    {
        $data = $this->api('people/' . $userId, 'GET', ['fields' => 'name,displayName,url,birthday,gender,image,currentLocation,nickname,aboutMe,relationshipStatus,urls,organizations,placesLived,tagline,emails,isPlusUser,braggingRights,plusOneCount,circledByCount,verified,cover,language,ageRange']);

        //echo '<pre>'; var_dump($data); echo '</pre>';die();

        return $data;
    }

}