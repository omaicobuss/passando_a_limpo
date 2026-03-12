<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\AccountProfileForm $profileForm */
/** @var app\models\ChangePasswordForm $passwordForm */
/** @var app\models\DeleteAccountForm $deleteAccountForm */
/** @var app\models\CandidateUpgradeRequestForm $candidateUpgradeRequestForm */
/** @var app\models\CandidateUpgradeRequest|null $latestCandidateUpgradeRequest */
/** @var array<string,int|string> $activitySummary */

use app\models\CandidateUpgradeRequest;
use app\models\DeleteAccountForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Minha Conta';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-account">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Gerencie seus dados pessoais, seguranca da conta e direitos LGPD.</p>

    <div class="row g-4">
        <div class="col-lg-7" id="dados">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Dados cadastrais</h2>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-update-profile'],
                        'method' => 'post',
                    ]); ?>

                    <?= $form->field($profileForm, 'username')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($profileForm, 'email')->input('email') ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Papel no sistema</label>
                                <input type="text" class="form-control" value="<?= Html::encode((string) $user->role) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Conta criada em</label>
                                <input type="text" class="form-control" value="<?= Html::encode(date('d/m/Y H:i', (int) $user->created_at)) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <?= Html::submitButton('Salvar dados', ['class' => 'btn btn-primary']) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="resumo">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Resumo dos seus dados</h2>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($activitySummary as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <?php if (!empty($item['url']) && (int) $item['value'] > 0): ?>
                                    <?= Html::a(Html::encode((string) $item['label']), $item['url'], ['class' => 'text-decoration-none']) ?>
                                <?php else: ?>
                                    <span><?= Html::encode((string) $item['label']) ?></span>
                                <?php endif; ?>
                                <span class="badge bg-secondary rounded-pill"><?= Html::encode((string) $item['value']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (!Yii::$app->user->can('candidate')): ?>
            <div class="col-12" id="candidatura">
                <div class="card shadow-sm border-warning">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Solicitacao para perfil candidate</h2>
                        <p class="text-muted mb-3">Envie um comprovante para analise do administrador. A mudanca de perfil depende de aprovacao manual.</p>

                        <?php if ($latestCandidateUpgradeRequest !== null): ?>
                            <?php
                            $statusClass = match ($latestCandidateUpgradeRequest->status) {
                                CandidateUpgradeRequest::STATUS_APPROVED => 'bg-success',
                                CandidateUpgradeRequest::STATUS_REJECTED => 'bg-danger',
                                default => 'bg-warning text-dark',
                            };
                            ?>
                            <p class="mb-1">
                                Ultima solicitacao:
                                <span class="badge <?= Html::encode($statusClass) ?>">
                                    <?= Html::encode($latestCandidateUpgradeRequest->getStatusLabel()) ?>
                                </span>
                            </p>
                            <p class="small text-muted mb-2">Enviada em <?= Html::encode(date('d/m/Y H:i', (int) $latestCandidateUpgradeRequest->created_at)) ?>.</p>

                            <?php if (!empty($latestCandidateUpgradeRequest->admin_notes)): ?>
                                <p class="small mb-2"><strong>Observacao do admin:</strong> <?= Html::encode($latestCandidateUpgradeRequest->admin_notes) ?></p>
                            <?php endif; ?>

                            <p class="mb-3">
                                <?= Html::a('Baixar comprovante enviado', ['site/candidate-request-document', 'id' => $latestCandidateUpgradeRequest->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($latestCandidateUpgradeRequest === null || $latestCandidateUpgradeRequest->status !== CandidateUpgradeRequest::STATUS_PENDING): ?>
                            <?php $form = ActiveForm::begin([
                                'action' => ['site/account-request-candidate'],
                                'method' => 'post',
                                'options' => ['enctype' => 'multipart/form-data'],
                            ]); ?>

                            <?= $form->field($candidateUpgradeRequestForm, 'document')->fileInput(['accept' => '.pdf,.jpg,.jpeg,.png']) ?>
                            <?= $form->field($candidateUpgradeRequestForm, 'message')->textarea(['rows' => 3, 'maxlength' => 2000]) ?>

                            <?= Html::submitButton('Enviar solicitacao', ['class' => 'btn btn-warning']) ?>

                            <?php ActiveForm::end(); ?>
                        <?php else: ?>
                            <div class="alert alert-warning mb-0">Sua solicitacao esta pendente. Aguarde a analise de um administrador.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-lg-6" id="seguranca">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Senha e seguranca</h2>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-change-password'],
                        'method' => 'post',
                    ]); ?>

                    <?= $form->field($passwordForm, 'current_password')->passwordInput() ?>
                    <?= $form->field($passwordForm, 'new_password')->passwordInput() ?>
                    <?= $form->field($passwordForm, 'new_password_repeat')->passwordInput() ?>

                    <?= Html::submitButton('Atualizar senha', ['class' => 'btn btn-outline-primary']) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6" id="lgpd">
            <div class="card shadow-sm border-danger">
                <div class="card-body">
                    <h2 class="h5 mb-3">LGPD e privacidade</h2>
                    <p class="text-muted mb-3">Voce pode baixar seus dados em formato JSON e solicitar exclusao definitiva da conta.</p>

                    <?= Html::beginForm(['site/account-export-data'], 'post', ['class' => 'mb-4']) ?>
                    <?= Html::submitButton('Exportar meus dados', ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::endForm() ?>

                    <hr>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-delete'],
                        'method' => 'post',
                    ]); ?>

                    <p class="small text-danger">A exclusao da conta e permanente e remove os dados vinculados no sistema.</p>
                    <?= $form->field($deleteAccountForm, 'password')->passwordInput() ?>
                    <?= $form->field($deleteAccountForm, 'confirmation')->textInput([
                        'placeholder' => DeleteAccountForm::CONFIRMATION_TEXT,
                    ]) ?>

                    <?= Html::submitButton('Excluir minha conta', [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Tem certeza que deseja excluir sua conta? Esta acao nao pode ser desfeita.',
                            'method' => 'post',
                        ],
                    ]) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
