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
              <?php if ($connectedClients) { ?>
                  <p>Поиск будет осуществляться по: </p>
                  <ul>
                  <?php foreach ($connectedClients as $client) { ?>
                      <li><?php echo $client; ?></li>
                  <?php } ?>
                  </ul>
                  
                  <?php
                  $form = ActiveForm::begin([
                      'method' => 'post',
                      'action' => ['search/index']
                  ]); ?>
                  <?= $form->field($formModel, 'q')->textInput(['name' => 'q']) ?>
                  <?= Html::submitButton('Search', ['class' => 'btn btn-primary search']) ?>
                  <?php ActiveForm::end() ?>
              <?php } else { ?>
                  <p>Поиск невозможен. Ни одна соц. сеть не подключена</p>              
              <?php } ?>

              

              <?php
              if (isset($results)) {
                  foreach ($results as $client => $profiles) { ?>
                      <p>Результаты поиска <?php echo $client; ?>:</p>
                      <?php foreach ($profiles as $profile) { ?>
                          <div class="profile-container">
                              <div class="profile-picture">
                                  <img src="<?php echo $profile['picture']; ?>" />
                              </div>
                              <div class="profile-data">
                                  <b><?php echo $profile['name']; ?></b>
                              </div>
                          </div>
                      <?php } ?>
                  <?php } ?>  
              <?php } ?>
              ?>
          </section>
      </div>
      <!-- Left side column. contains the logo and sidebar -->
