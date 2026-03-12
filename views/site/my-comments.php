<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalComment;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Meus comentários';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-comments app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Participação no debate</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Consulte rapidamente onde você comentou, em que contexto e com qual status de moderação.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>comentário(s) publicado(s)</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não publicou nenhum comentário.</div>
    <?php else: ?>
        <div class="app-record-stack">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalComment $model */ ?>
                <?php
                if ($model->isDeleted()) {
                    $statusLabel = 'Excluído';
                    $statusClass = 'app-record-chip--danger';
                } elseif ($model->is_inappropriate) {
                    $statusLabel = 'Marcado como inapropriado';
                    $statusClass = 'app-record-chip--accent';
                } else {
                    $statusLabel = 'Ativo';
                    $statusClass = 'app-record-chip--success';
                }
                ?>
                <article class="app-record-card app-record-card--comment">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $statusClass ?>"><?= $statusLabel ?></span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) ($model->proposal?->title ?? 'Proposta indisponível')) ?></h2>
                    <p class="app-record-card__text<?= $model->isDeleted() ? ' fst-italic' : '' ?>">
                        <?= Html::encode($model->isDeleted() ? $model->getDisplayContent() : StringHelper::truncateWords((string) $model->content, 28, '...')) ?>
                    </p>
                    <div class="app-record-meta">
                        <span><strong>Data</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
                        <span><strong>Resposta a</strong> <?= $model->parent_id ? 'outro comentário' : 'comentário raiz' ?></span>
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
