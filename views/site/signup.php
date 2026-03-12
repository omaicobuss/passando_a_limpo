<?php

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Criar conta';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Cadastre-se como cidadão para participar da plataforma.</p>

    <div class="row">
        <div class="col-lg-6">
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'email')->input('email') ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= Html::submitButton('Cadastrar', ['class' => 'btn btn-success']) ?>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
