<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\CandidateUpgradeRequest;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Solicitações de candidatura';
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-candidate-requests app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Moderação administrativa</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Analise solicitações de mudança de perfil com contexto, comprovante e decisão registrada no mesmo cartão.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>solicitação(ões) no fluxo</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="app-empty-state">
            <h2 class="h5 mb-2">Nenhuma solicitação no momento</h2>
            <p class="mb-0">Novos pedidos de mudança para o perfil de candidato aparecerão aqui para revisão.</p>
        </div>
    <?php else: ?>
        <div class="app-record-stack">
            <?php foreach ($models as $model): ?>
                <?php /** @var CandidateUpgradeRequest $model */ ?>
                <?php
                $statusClass = match ($model->status) {
                    CandidateUpgradeRequest::STATUS_APPROVED => 'app-record-chip--success',
                    CandidateUpgradeRequest::STATUS_REJECTED => 'app-record-chip--danger',
                    default => 'app-record-chip--accent',
                };
                $reviewSummary = '-';
                if ($model->reviewed_by !== null) {
                    $reviewSummary = sprintf(
                        '%s em %s',
                        $model->reviewer->username ?? '#' . $model->reviewed_by,
                        $model->reviewed_at ? date('d/m/Y H:i', (int) $model->reviewed_at) : '-'
                    );
                }
                ?>
                <article class="app-record-card app-record-card--admin">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $statusClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <h2 class="app-record-card__title mb-2"><?= Html::encode((string) ($model->user->username ?? '#' . $model->user_id)) ?></h2>
                            <div class="app-record-meta mb-3">
                                <span><strong>Enviada em</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
                                <span><strong>Revisão</strong> <?= Html::encode($reviewSummary) ?></span>
                            </div>
                            <p class="app-record-card__text mb-3">
                                <?= Html::encode(trim((string) $model->message) !== '' ? (string) $model->message : 'O usuário não deixou observações adicionais para esta solicitação.') ?>
                            </p>
                            <?php if ((string) $model->admin_notes !== ''): ?>
                                <div class="app-inline-note">
                                    <strong>Observação administrativa</strong>
                                    <p class="mb-0"><?= Html::encode((string) $model->admin_notes) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-lg-5">
                            <div class="app-side-panel h-100">
                                <div class="app-side-panel__row">
                                    <span>Comprovante</span>
                                    <?= Html::a('Baixar arquivo', ['site/candidate-request-document', 'id' => $model->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                                </div>

                                <?php if ($model->status === CandidateUpgradeRequest::STATUS_PENDING): ?>
                                    <div class="app-admin-action-group">
                                        <?= Html::beginForm(['site/candidate-request-review', 'id' => $model->id, 'decision' => 'approve'], 'post', ['class' => 'app-admin-action-form']) ?>
                                        <?= Html::textInput('admin_notes', '', ['class' => 'form-control mb-2', 'placeholder' => 'Observação para aprovação (opcional)']) ?>
                                        <?= Html::submitButton('Aprovar solicitação', ['class' => 'btn btn-success app-btn w-100']) ?>
                                        <?= Html::endForm() ?>

                                        <?= Html::beginForm(['site/candidate-request-review', 'id' => $model->id, 'decision' => 'reject'], 'post', ['class' => 'app-admin-action-form']) ?>
                                        <?= Html::textInput('admin_notes', '', ['class' => 'form-control mb-2', 'placeholder' => 'Motivo da reprovação']) ?>
                                        <?= Html::submitButton('Reprovar solicitação', ['class' => 'btn btn-danger app-btn w-100']) ?>
                                        <?= Html::endForm() ?>
                                    </div>
                                <?php else: ?>
                                    <div class="app-side-panel__note">
                                        <strong>Fluxo concluído</strong>
                                        <p class="mb-0">Esta solicitação já foi analisada e está registrada no histórico com o parecer acima.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination justify-content-center mt-4'],
        ]) ?>
    <?php endif; ?>
</div>
