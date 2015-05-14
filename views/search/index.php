<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm; 
use app\components\helpers\ViewHelper;

?>  
<?php if ($connectedClients) { ?>
    <p>Поиск будет осуществляться по: </p>
    <ul>
        <?php foreach ($connectedClients as $client) { ?>
            <li><?php echo ViewHelper::getClientNameBySource($client->source); ?></li>
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
    <?php foreach ($connectedClients as $client) { ?>
        <div class="search-block" id="<?php echo $client->source; ?>">
            <div class="title">Поиск по <?php echo ViewHelper::getClientNameBySource($client->source); ?>:</div>
            <div class="profiles"></div>
        </div>
    <?php } ?>
</div>
