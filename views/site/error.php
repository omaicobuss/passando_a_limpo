<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error d-flex justify-content-center align-items-center py-5">
    <div class="card app-filter-card text-center" style="width: 100%; max-width: 600px;">
        <div class="card-body p-4 p-md-5">
            <span class="app-section-eyebrow text-danger">Erro no processamento</span>
            <h1 class="h3 mt-3 mb-4"><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-danger mb-4 text-start">
                <?= nl2br(Html::encode($message)) ?>
            </div>

            <p class="text-muted mb-2">
                O erro acima ocorreu enquanto o servidor tentava processar sua requisição.
            </p>
            <p class="text-muted mb-4">
                Por favor, entre em contato conosco se você acredita que isso é um erro do servidor. Obrigado.
            </p>
            
            <?= Html::a('Voltar ao início', ['site/index'], ['class' => 'btn btn-primary app-btn']) ?>
        </div>
    </div>
</div>
