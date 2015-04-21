<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\VKontakte;
use yii\base\Exception;
use app\components\SearchResult;

class PSVKontakte extends VKontakte
{
    protected function getNormalizeSearchResultMap()
    {
        return [
            'id' => 'uid',
            'name' => function ($data) {
                return $data['first_name'] . ' ' . $data['last_name'];
            },
            'picture' => 'photo_50',
            'picture_big' => 'photo_200_orig',
            'mobile_phone' => function($data) {
                return (isset($data['mobile_phone']) && $data['mobile_phone']) ? $data['mobile_phone'] : NULL;
            },
            'home_phone' => function($data) {
                return (isset($data['home_phone']) && $data['home_phone']) ? $data['home_phone'] : NULL;
            },
            'profile_url' => function($data) {
                return 'https://vk.com/' . $data['screen_name'];
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
        //$data = $this->api('users.search', 'GET', ['q' => $query, 'fields' => 'contacts,photo_50,photo_200_orig,screen_name', 'count' => 50]);
        $data = $this->api('users.search', 'GET', ['q' => $queryString, 'v' => '5.30', 'fields' => 'nickname,screen_name,sex,bdate,city,country,timezone,photo_50,photo_100,photo_200_orig,has_mobile,contacts,education,online,relation,last_seen,status,can_write_private_message,can_see_all_posts,can_post,universities,domain', 'count' => $limit, 'offset' => $offset]);
        $totalProfilesCount = $data['response']['count'];    
        $queryItemsCount = count($data['response']['items']);

        $result = $this->normalizeSearchResult($data['response']['items']);
        $searchResult = new SearchResult($this->getId(), $result);

        if (($offset + $queryItemsCount) < $totalProfilesCount) {
            $nextQueryOffset = $offset + $limit;
            $searchResult->setOffset($nextQueryOffset);
        } else {
            $searchResult->setOffset(FALSE);
        }

        return $searchResult;
    }
}