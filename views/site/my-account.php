<?php

/** @var yii\web\View $this */
/** @var app\models\User $user */
/** @var app\models\AccountProfileForm $profileForm */
/** @var app\models\ChangePasswordForm $passwordForm */
/** @var app\models\DeleteAccountForm $deleteAccountForm */
/** @var app\models\CandidateUpgradeRequestForm $candidateUpgradeRequestForm */
/** @var app\models\CandidateUpgradeRequest|null $latestCandidateUpgradeRequest */
/** @var array<int,array{label:string,value:int|string,url?:array}> $activitySummary */

use app\models\CandidateUpgradeRequest;
use app\models\DeleteAccountForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Minha Conta';
$this->params['breadcrumbs'][] = $this->title;

$candidateRequestStatusClass = null;
if ($latestCandidateUpgradeRequest !== null) {
    $candidateRequestStatusClass = match ($latestCandidateUpgradeRequest->status) {
        CandidateUpgradeRequest::STATUS_APPROVED => 'bg-success',
        CandidateUpgradeRequest::STATUS_REJECTED => 'bg-danger',
        default => 'bg-warning text-dark',
    };
}
?>
<div class="site-my-account account-page">
    <section class="account-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Conta e preferências</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Gerencie dados pessoais, segurança, direitos LGPD e o histórico da sua participação pública em uma visão consolidada.</p>
                <div class="account-hero__meta mt-4">
                    <span><strong>Usuário</strong> <?= Html::encode((string) $user->username) ?></span>
                    <span><strong>Email</strong> <?= Html::encode((string) $user->email) ?></span>
                    <span><strong>Papel</strong> <?= Html::encode((string) $user->role) ?></span>
                    <span><strong>Desde</strong> <?= date('d/m/Y H:i', (int) $user->created_at) ?></span>
                </div>
                <div class="account-hero__nav mt-4 d-flex flex-wrap gap-2">
                    <?= Html::a('Resumo', '#resumo', ['class' => 'btn btn-outline-light app-btn app-btn--light']) ?>
                    <?= Html::a('Dados', '#dados', ['class' => 'btn btn-outline-light app-btn app-btn--light']) ?>
                    <?php if (!Yii::$app->user->can('candidate')): ?>
                        <?= Html::a('Candidatura', '#candidatura', ['class' => 'btn btn-outline-light app-btn app-btn--light']) ?>
                    <?php endif; ?>
                    <?= Html::a('Segurança', '#seguranca', ['class' => 'btn btn-outline-light app-btn app-btn--light']) ?>
                    <?= Html::a('LGPD', '#lgpd', ['class' => 'btn btn-outline-light app-btn app-btn--light']) ?>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="account-hero__side">
                    <span class="account-hero__side-label">Resumo operacional</span>
                    <strong><?= count($activitySummary) ?> áreas monitoradas</strong>
                    <p class="mb-0">Os atalhos abaixo levam para visões consolidadas de candidaturas, propostas, comentários, votos e atualizações.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="account-section mb-4" id="resumo">
        <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
            <div>
                <span class="app-section-eyebrow">Mapa da sua atividade</span>
                <h2 class="h4 mt-3 mb-0">Resumo dos seus dados</h2>
            </div>
            <span class="home-score-chip"><?= count($activitySummary) ?> indicadores</span>
        </div>

        <div class="account-activity-grid">
            <?php foreach ($activitySummary as $item): ?>
                <?php
                $isLinked = !empty($item['url']) && (int) $item['value'] > 0;
                $cardContent = Html::tag('span', Html::encode((string) $item['label']), ['class' => 'account-activity-card__label'])
                    . Html::tag('strong', Html::encode((string) $item['value']), ['class' => 'account-activity-card__value'])
                    . Html::tag('span', $isLinked ? 'Abrir visão consolidada' : 'Sem registros disponíveis', ['class' => 'account-activity-card__hint']);
                ?>
                <?php if ($isLinked): ?>
                    <?= Html::a($cardContent, $item['url'], ['class' => 'account-activity-card account-activity-card--linked']) ?>
                <?php else: ?>
                    <div class="account-activity-card"><?= $cardContent ?></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="row g-4 align-items-start">
        <div class="col-xl-7" id="dados">
            <section class="card account-section-card h-100">
                <div class="card-body">
                    <span class="app-section-eyebrow">Dados cadastrais</span>
                    <h2 class="h4 mt-3 mb-2">Atualize suas informações principais</h2>
                    <p class="mb-4">Mantenha o nome de usuário e o email corretos para preservar notificações e acessos do sistema.</p>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-update-profile'],
                        'method' => 'post',
                    ]); ?>

                    <?= $form->field($profileForm, 'username')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($profileForm, 'email')->input('email') ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="account-readonly-field">
                                <span>Papel no sistema</span>
                                <strong><?= Html::encode((string) $user->role) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="account-readonly-field">
                                <span>Conta criada em</span>
                                <strong><?= Html::encode(date('d/m/Y H:i', (int) $user->created_at)) ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <?= Html::submitButton('Salvar dados', ['class' => 'btn btn-primary app-btn']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </section>
        </div>

        <div class="col-xl-5">
            <section class="card account-section-card h-100">
                <div class="card-body">
                    <span class="app-section-eyebrow">Visão geral</span>
                    <h2 class="h4 mt-3 mb-2">Sua conta em poucas linhas</h2>
                    <div class="account-overview-list mt-4">
                        <div class="account-overview-item">
                            <span>Status da conta</span>
                            <strong>Ativa</strong>
                        </div>
                        <div class="account-overview-item">
                            <span>Perfil atual</span>
                            <strong><?= Html::encode((string) $user->role) ?></strong>
                        </div>
                        <div class="account-overview-item">
                            <span>Exportação de dados</span>
                            <strong>Disponível sob demanda</strong>
                        </div>
                        <div class="account-overview-item">
                            <span>Candidatura</span>
                            <strong><?= Yii::$app->user->can('candidate') ? 'Perfil habilitado' : 'Depende de aprovação' ?></strong>
                        </div>
                    </div>

                    <?php if ($latestCandidateUpgradeRequest !== null): ?>
                        <div class="app-inline-note mt-4">
                            <strong>Última solicitação de candidatura</strong>
                            <p class="mb-2">
                                <span class="badge <?= Html::encode((string) $candidateRequestStatusClass) ?>">
                                    <?= Html::encode($latestCandidateUpgradeRequest->getStatusLabel()) ?>
                                </span>
                            </p>
                            <p class="mb-0">Enviada em <?= Html::encode(date('d/m/Y H:i', (int) $latestCandidateUpgradeRequest->created_at)) ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php if (!Yii::$app->user->can('candidate')): ?>
            <div class="col-12" id="candidatura">
                <section class="card account-section-card account-section-card--accent">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-start">
                            <div class="account-section-card__intro">
                                <span class="app-section-eyebrow">Evolução de perfil</span>
                                <h2 class="h4 mt-3 mb-2">Solicitação para perfil candidate</h2>
                                <p class="mb-0">Envie um comprovante para análise do administrador. A mudança de perfil depende de aprovação manual e fica registrada no seu histórico.</p>
                            </div>
                            <?php if ($latestCandidateUpgradeRequest !== null): ?>
                                <div class="account-request-summary">
                                    <span class="badge <?= Html::encode((string) $candidateRequestStatusClass) ?> mb-2">
                                        <?= Html::encode($latestCandidateUpgradeRequest->getStatusLabel()) ?>
                                    </span>
                                    <p class="mb-1">Última solicitação enviada em <?= Html::encode(date('d/m/Y H:i', (int) $latestCandidateUpgradeRequest->created_at)) ?>.</p>
                                    <?php if (!empty($latestCandidateUpgradeRequest->admin_notes)): ?>
                                        <p class="mb-0"><strong>Observação do admin:</strong> <?= Html::encode($latestCandidateUpgradeRequest->admin_notes) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($latestCandidateUpgradeRequest !== null): ?>
                            <div class="mt-4">
                                <?= Html::a('Baixar comprovante enviado', ['site/candidate-request-document', 'id' => $latestCandidateUpgradeRequest->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($latestCandidateUpgradeRequest === null || $latestCandidateUpgradeRequest->status !== CandidateUpgradeRequest::STATUS_PENDING): ?>
                            <div class="mt-4">
                                <?php $form = ActiveForm::begin([
                                    'action' => ['site/account-request-candidate'],
                                    'method' => 'post',
                                    'options' => ['enctype' => 'multipart/form-data'],
                                ]); ?>

                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <?= $form->field($candidateUpgradeRequestForm, 'document')->fileInput(['accept' => '.pdf,.jpg,.jpeg,.png']) ?>
                                    </div>
                                    <div class="col-lg-7">
                                        <?= $form->field($candidateUpgradeRequestForm, 'message')->textarea(['rows' => 4, 'maxlength' => 2000]) ?>
                                    </div>
                                </div>

                                <?= Html::submitButton('Enviar solicitacao', ['class' => 'btn btn-warning app-btn']) ?>

                                <?php ActiveForm::end(); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4 mb-0">Sua solicitação está pendente. Aguarde a análise de um administrador.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        <?php endif; ?>

        <div class="col-lg-6" id="seguranca">
            <section class="card account-section-card">
                <div class="card-body">
                    <span class="app-section-eyebrow">Segurança</span>
                    <h2 class="h4 mt-3 mb-2">Senha e proteção da conta</h2>
                    <p class="mb-4">Altere sua senha sempre que houver suspeita de compartilhamento indevido ou necessidade de reforçar o acesso.</p>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-change-password'],
                        'method' => 'post',
                    ]); ?>

                    <?= $form->field($passwordForm, 'current_password')->passwordInput() ?>
                    <?= $form->field($passwordForm, 'new_password')->passwordInput() ?>
                    <?= $form->field($passwordForm, 'new_password_repeat')->passwordInput() ?>

                    <?= Html::submitButton('Atualizar senha', ['class' => 'btn btn-outline-primary app-btn app-btn--ghost']) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </section>
        </div>

        <div class="col-lg-6" id="lgpd">
            <section class="card account-section-card account-section-card--danger">
                <div class="card-body">
                    <span class="app-section-eyebrow">Privacidade</span>
                    <h2 class="h4 mt-3 mb-2">LGPD e gestão de dados</h2>
                    <p class="mb-4">Baixe seus dados em JSON ou solicite exclusão definitiva da conta quando necessário.</p>

                    <div class="account-privacy-actions mb-4">
                        <?= Html::beginForm(['site/account-export-data'], 'post', ['class' => 'm-0']) ?>
                        <?= Html::submitButton('Exportar meus dados', ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                        <?= Html::endForm() ?>
                    </div>

                    <?php $form = ActiveForm::begin([
                        'action' => ['site/account-delete'],
                        'method' => 'post',
                    ]); ?>

                    <p class="small text-danger">A exclusão da conta é permanente e remove os dados vinculados no sistema.</p>
                    <?= $form->field($deleteAccountForm, 'password')->passwordInput() ?>
                    <?= $form->field($deleteAccountForm, 'confirmation')->textInput([
                        'placeholder' => DeleteAccountForm::CONFIRMATION_TEXT,
                    ]) ?>

                    <?= Html::submitButton('Excluir minha conta', [
                        'class' => 'btn btn-danger app-btn',
                        'data' => [
                            'confirm' => 'Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.',
                            'method' => 'post',
                        ],
                    ]) ?>

                    <?php ActiveForm::end(); ?>
                </div>
            </section>
        </div>
    </div>
</div>
