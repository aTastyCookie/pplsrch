<?php

namespace app\models;

use Yii;
use yii\base\Model;

class SearchForm extends Model
{
    public $q;

    public function attributeLabels()
    {
        return [
            'q' => 'Search query:',
        ];
    }

}

