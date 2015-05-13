<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm; 

?>  
              <?php if ($connectedClients) { ?>
                  <p>Поиск будет осуществляться по: </p>
                  <ul>
                  <?php foreach ($connectedClients as $client) { ?>
                      <li><?php echo $client->source; ?></li>
                      <input class="client-hidden" type="hidden" value="<?php echo $client->source; ?>"> 
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
              <div id="search-results">
                  <div class="search-results" id="vk">
                      <div class="title">Поиск по VK:</div>
                      <div class="profiles"></div>
                  </div>
                  <div class="search-results" id="fb">
                      <div class="title">Поиск по Facebook:</div>
                      <div class="profiles"></div>
                  </div>
                  <div class="search-results" id="tw">
                      <div class="title">Поиск по Twitter:</div>
                      <div class="profiles"></div>
                  </div>
                  <div class="search-results" id="gg">
                      <div class="title">Поиск по Google+:</div>
                      <div class="profiles"></div>
                  </div>   
              </div>
