<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm; 

?>
      <header class="main-header">
        <!-- Logo -->
        <a href="index2.html" class="logo"><b>People</b>Search</a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">      
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="<?= Html::encode($user->photo) ?>" class="user-image" alt="User Image"/>
                  <span class="hidden-xs"><?= Html::encode($user->name) ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="<?= Html::encode($user->photo) ?>" class="img-circle" alt="User Image" />
                    <p>
                      <?= Html::encode($user->name) ?>
                    </p>
                  </li>
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-right">
                      <a href="<?= Url::toRoute('site/logout'); ?>" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <ul class="sidebar-menu">
              <li><a href="<?= Url::toRoute(['index']); ?>">Search</a></li>
              <li><a href="<?= Url::toRoute(['connect']); ?>">Connect</a></li>
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>
      <div class="content-wrapper">
          <section class="content">
              <span><b>Facebook:</b></span>]

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
          </section>
      </div>
      <style>
          .enabled {
            background: green;
            color: #fff;
          }
      </style>
      <!-- Left side column. contains the logo and sidebar -->
