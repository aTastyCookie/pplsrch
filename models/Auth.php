<?php

namespace app\models;

use yii\db\ActiveRecord;

class Auth extends ActiveRecord { 

    public function getUser()
    {
        $user = User::find()->where([
        	'id' => $this->user_id
        ])->one();

        return $user;
    }
}