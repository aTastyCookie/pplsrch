<?php 

namespace app\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@bower/admin-lte';
    public $css = [
        'dist/css/AdminLTE.min.css',
        'dist/css/skins/skin-blue.min.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];
}