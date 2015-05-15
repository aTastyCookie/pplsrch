<?php

namespace app\models;

use yii\base\Model;

class SettingsForm extends Model
{
    public $clientMaxResults;

    public function rules()
    {
    	return [
    	    [['clientMaxResults'], 'safe'],
    	];
    }
}