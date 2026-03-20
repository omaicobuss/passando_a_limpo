<?php

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Criar conta';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup d-flex justify-content-center align-items-center py-5">
    <div class="card app-filter-card" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4 p-md-5">
            <span class="app-section-eyebrow">Novo acesso</span>
            <h1 class="h3 mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
            <p class="mb-4 text-muted">Cadastre-se como cidadão para participar da plataforma.</p>

            <?php $form = ActiveForm::begin(['options' => ['class' => 'app-form']]); ?>
            
            <div class="mb-3">
                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            </div>
            
            <div class="mb-3">
                <?= $form->field($model, 'email')->input('email') ?>
            </div>
            
            <div class="mb-4">
                <?= $form->field($model, 'password')->passwordInput() ?>
            </div>
            
            <div class="d-grid mt-4">
                <?= Html::submitButton('Cadastrar', ['class' => 'btn btn-primary app-btn']) ?>
            </div>
            
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
