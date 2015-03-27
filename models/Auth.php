<?php

namespace app\models;

use yii\db\ActiveRecord;

class Auth extends ActiveRecord {
    
    public $user = 'Test';
    /*public $id;
    public $user_id;
    public $source;
    public $source_id;

    public static function tableName()
    {
        return 'auth';
    }*/ 

    public function getUser()
    {
        $user = User::find()->where([
        	'id' => $this->user_id
        ])->one();

        return $user;
    }
}