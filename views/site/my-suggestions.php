<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalSuggestion;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Minhas sugestões em propostas';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-suggestions app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Colaboração em propostas</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Acompanhe o destino das melhorias que você sugeriu, com status, pontuação e acesso direto à proposta original.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>sugestão(ões) enviada(s)</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não enviou nenhuma sugestão.</div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalSuggestion $model */ ?>
                <?php
                $statusClass = match ($model->status) {
                    ProposalSuggestion::STATUS_APPROVED => 'app-record-chip--success',
                    ProposalSuggestion::STATUS_REJECTED => 'app-record-chip--danger',
                    default => 'app-record-chip--accent',
                };
                $statusLabel = ProposalSuggestion::statusOptions()[$model->status] ?? $model->status;
                ?>
                <article class="app-record-card app-record-card--proposal">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $statusClass ?>"><?= Html::encode((string) $statusLabel) ?></span>
                        <span class="home-score-chip">Score <?= $model->getScore() ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) $model->title) ?></h2>
                    <p class="app-record-card__text"><?= Html::encode(mb_strimwidth((string) $model->content, 0, 160, '...')) ?></p>
                    <div class="app-record-meta">
                        <span><strong>Proposta</strong> <?= Html::encode((string) ($model->proposal?->title ?? '-')) ?></span>
                        <span><strong>Data</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
                    </div>
                    <div class="app-record-card__actions">
                        <?= Html::a('Ver proposta', ['/proposal/view', 'id' => $model->proposal_id], ['class' => 'btn btn-primary app-btn']) ?>
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
