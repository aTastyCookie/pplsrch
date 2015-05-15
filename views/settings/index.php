<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

$form = ActiveForm::begin([
    'id' => 'settings-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
<div style="overflow:hidden;">
    <div class="col-xs-3">
        <?php echo $form->field($settingsModel, 'clientMaxResults')->dropDownList(['5' => '5', '10' => '10', '15' => '15', '20' => '20'])->label('Количество результатов по каждой соц. сети'); ?>
    </div>
</div>
<?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>