<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Entrar';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login d-flex justify-content-center align-items-center py-5">
    <div class="card app-filter-card" style="width: 100%; max-width: 450px;">
        <div class="card-body p-4 p-md-5">
            <span class="app-section-eyebrow">Autenticação</span>
            <h1 class="h3 mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
            <p class="mb-4 text-muted">Informe usuário e senha para acessar sua conta.</p>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>

            <div class="mb-3">
                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            </div>

            <div class="mb-3">
                <?= $form->field($model, 'password')->passwordInput() ?>
            </div>

            <div class="mb-4">
                <?= $form->field($model, 'rememberMe')->checkbox([
                    'template' => "<div class=\"form-check\">{input} {label}</div>\n<div class=\"invalid-feedback d-block\">{error}</div>",
                ]) ?>
            </div>

            <div class="d-grid mb-4">
                <?= Html::submitButton('Entrar', ['class' => 'btn btn-primary app-btn', 'name' => 'login-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>

            <div class="app-inline-note">
                <strong>Dica de acesso:</strong> Usuário administrador inicial: <strong>admin</strong> com senha <strong>admin123</strong>.
            </div>
        </div>
    </div>
</div>
