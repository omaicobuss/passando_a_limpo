<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Fale conosco</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Se você tem dúvidas, sugestões ou problemas técnicos, preencha o formulário abaixo.</p>
            </div>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>
        <div class="alert alert-success mt-4">
            Thank you for contacting us. We will respond to you as soon as possible.
        </div>

        <p class="mt-3">
            Note that if you turn on the Yii debugger, you should be able
            to view the mail message on the mail panel of the debugger.
            <?php if (Yii::$app->mailer->useFileTransport): ?>
                Because the application is in development mode, the email is not sent but saved as
                a file under <code><?= Yii::getAlias(Yii::$app->mailer->fileTransportPath) ?></code>.
                Please configure the <code>useFileTransport</code> property of the <code>mail</code>
                application component to be false to enable email sending.
            <?php endif; ?>
        </p>
    <?php else: ?>
        <div class="card app-filter-card mt-4">
            <div class="card-body p-4 p-md-5">
                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <?php $form = ActiveForm::begin(['id' => 'contact-form', 'options' => ['class' => 'app-form']]); ?>

                            <div class="mb-3">
                                <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
                            </div>

                            <div class="mb-3">
                                <?= $form->field($model, 'email') ?>
                            </div>

                            <div class="mb-3">
                                <?= $form->field($model, 'subject') ?>
                            </div>

                            <div class="mb-3">
                                <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>
                            </div>

                            <div class="mb-4">
                                <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                                    'template' => '<div class="row"><div class="col-lg-4 mb-2 mb-lg-0">{image}</div><div class="col-lg-8">{input}</div></div>',
                                ]) ?>
                            </div>

                            <div class="mt-4">
                                <?= Html::submitButton('Enviar mensagem', ['class' => 'btn btn-primary app-btn', 'name' => 'contact-button']) ?>
                            </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
