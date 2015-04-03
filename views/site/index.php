<?php use yii\helpers\Url; ?>

<h1>Авторизируйтесь или зарегистрируйтесь!</h1>
<div class="register">
    <h2>Регистрация через Facebook</h2>
    <a class="auth-link facebook" href="<?= Url::toRoute(['auth', 'authclient' => 'facebook']); ?>">
        <span class="auth-icon facebook"></span>
    </a>
</div>
<div class="auth">
    <h2>Авторизация</h2>
    <?= yii\authclient\widgets\AuthChoice::widget([
         'baseAuthUrl' => ['site/auth']
    ]) ?>
</div>
<style>
h1 {
	text-align: center;
}
h2 {
    margin-top: 0;
}
.register,
.auth {
	text-align: center;
    width: 600px;
    margin: 50px auto;
    border-radius: 10px;
    border: 1px solid #eeeeee;
    padding: 25px;
}
</style>