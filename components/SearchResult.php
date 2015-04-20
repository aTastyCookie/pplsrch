<?php

namespace app\components;

use yii\base\Object; 

class SearchResult extends Object
{
	private $_profiles;
	private $_count;
	private $_offset;
	private $_clientId;

	public function __construct($clientId, $profiles, $count = 50, $offset = 0)
	{
        $this->_profiles = $profiles;
        $this->_count = $count;
        $this->_offset = $offset;
        $this->_clientId = $clientId;
	}

	public function getProfiles()
	{
		return $this->_profiles;
	}

	public function getClientId()
	{
		return $this->_clientId;
	}

	public function canGetMore()
	{
		return $this->_offset ? TRUE : FALSE;
	}

	public function getOffset()
	{
		return $this->_offset;
	}

	public function setOffset($offset)
	{
		$this->_offset = $offset;
	}
}