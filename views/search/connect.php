<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm; 
?>
              <span><b>Facebook:</b></span>

              <?php if (array_key_exists('facebook', $auths) && $auths['facebook']['status']) { ?>
                  &nbsp;<span class="enabled">connected</span>&nbsp;&nbsp;&nbsp;<a href="<?= Url::toRoute(['disconnect', 'authclient' => 'facebook']); ?>">disconnect</a>
              <?php } else { ?>
                  <a href="<?= Url::toRoute(['auth', 'authclient' => 'facebook']); ?>  ">connect</a>  
              <?php } ?>
              <br /><br />
              <span><b>Twitter:</b></span>
              <?php if (array_key_exists('twitter', $auths) && $auths['twitter']['status']) { ?>
                  &nbsp;<span class="enabled">connected</span>&nbsp;&nbsp;&nbsp;<a href="<?= Url::toRoute(['disconnect', 'authclient' => 'twitter']); ?>">disconnect</a>
              <?php } else { ?>
                  <a href="<?= Url::toRoute(['auth', 'authclient' => 'twitter']); ?>  ">connect</a>  
              <?php } ?>
              <br /><br />
              <span><b>VK:</b></span>
              <?php if (array_key_exists('vkontakte', $auths) && $auths['vkontakte']['status']) { ?>
                  &nbsp;<span class="enabled">connected</span>&nbsp;&nbsp;&nbsp;<a href="<?= Url::toRoute(['disconnect', 'authclient' => 'vkontakte']); ?>">disconnect</a>
              <?php } else { ?>
                  <a href="<?= Url::toRoute(['auth', 'authclient' => 'vkontakte']); ?>  ">connect</a>  
              <?php } ?>
              <br /><br />
              <span><b>Google+:</b></span>
              <?php if (array_key_exists('google', $auths) && $auths['google']['status']) { ?>
                  &nbsp;<span class="enabled">connected</span>&nbsp;&nbsp;&nbsp;<a href="<?= Url::toRoute(['disconnect', 'authclient' => 'google']); ?>">disconnect</a>
              <?php } else { ?>
                  <a href="<?= Url::toRoute(['auth', 'authclient' => 'google']); ?>  ">connect</a>  
              <?php } ?>
              <br /><br />
              <span><b>LinkedIn:</b></span>
              <?php if (array_key_exists('linkedin', $auths) && $auths['linkedin']['status']) { ?>
                  &nbsp;<span class="enabled">connected</span>&nbsp;&nbsp;&nbsp;<a href="<?= Url::toRoute(['disconnect', 'authclient' => 'linkedin']); ?>">disconnect</a>
              <?php } else { ?>
                  <a href="<?= Url::toRoute(['auth', 'authclient' => 'linkedin']); ?>  ">connect</a>  
              <?php } ?>
              <br /><br />
          
      <style>
          .enabled {
            background: green;
            color: #fff;
          }
      </style>
