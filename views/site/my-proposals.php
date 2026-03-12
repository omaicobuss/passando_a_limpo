<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Proposal;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Minhas propostas publicadas';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-proposals app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Portfólio de propostas</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Acompanhe score, status e contexto das propostas que você já publicou na plataforma.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>proposta(s) publicada(s)</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não publicou nenhuma proposta.</div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var Proposal $model */ ?>
                <?php $statusLabel = Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status; ?>
                <article class="app-record-card app-record-card--proposal">
                    <div class="app-record-card__header">
                        <span class="app-record-chip app-record-chip--soft"><?= Html::encode((string) $statusLabel) ?></span>
                        <span class="home-score-chip">Score <?= (int) $model->score ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) $model->title) ?></h2>
                    <p class="app-record-card__text"><?= Html::encode(mb_strimwidth(strip_tags((string) $model->content), 0, 160, '...')) ?></p>
                    <div class="app-record-meta">
                        <span><strong>Tema</strong> <?= Html::encode((string) ($model->theme ?: 'Tema geral')) ?></span>
                        <span><strong>Eleição</strong> <?= Html::encode((string) ($model->election?->title ?? '-')) ?></span>
                        <span><strong>Publicada em</strong> <?= date('d/m/Y', (int) $model->created_at) ?></span>
                    </div>
                    <div class="app-record-card__actions">
                        <?= Html::a('Abrir proposta', ['/proposal/view', 'id' => $model->id], ['class' => 'btn btn-primary app-btn']) ?>
                        <?php if (Yii::$app->user->can('updateOwnProposal', ['proposal' => $model]) && !$model->isEditLockedByElectionDeadline()): ?>
                            <?= Html::a('Editar', ['/proposal/update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                        <?php endif; ?>
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
