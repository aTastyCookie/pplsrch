<?php

namespace app\components\authclient\clients;

use yii\authclient\clients\Twitter;
use app\components\SearchResult;

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
            'picture' => 'profile_image_url',
            'picture_big' => function($data) {
                return preg_replace('|_normal|', '', $data['profile_image_url']);
            },
            'profile_url' => function($data) {
                return 'https://twitter.com/' . $data['screen_name'];
            },
            'email' => function() {
                return NULL;
            },
            'mobile_phone' => function() {
                return NULL;
            },
            'home_phone' => function() {
                return NULL;
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
        if ($offset) {
            $page = $offset;
        } else {
            $page = 1;
        }

        $data = $this->api('users/search.json', 'GET', ['q' => $queryString, 'page' => $page, 'count' => $limit]);
        $queryItemsCount = count($data);

        $result = $this->normalizeSearchResult($data);
        $lastElementId = end($result);

        $searchResult = new SearchResult($this->getId(), $result);

        if ($queryItemsCount == $limit && $data[$limit-1]['id'] != $after) {
            $nextQueryOffset = $page + 1;
            $searchResult->setOffset($nextQueryOffset);
            $searchResult->setAfter($lastElementId['id']);
        } else {
            $searchResult->setOffset(FALSE);
            $searchResult->setAfter(FALSE);
        }

        return $searchResult;
    }
}
