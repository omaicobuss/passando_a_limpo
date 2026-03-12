<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalSuggestionVote;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Meus votos em sugestões';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-suggestion-votes app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Histórico de votos</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Consulte as sugestões que você avaliou e retorne ao contexto original da proposta quando precisar.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>voto(s) em sugestões</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não votou em nenhuma sugestão.</div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalSuggestionVote $model */ ?>
                <?php $isPositive = (int) $model->value > 0; ?>
                <article class="app-record-card app-record-card--vote">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $isPositive ? 'app-record-chip--success' : 'app-record-chip--danger' ?>">
                            <?= $isPositive ? 'Voto positivo' : 'Voto negativo' ?>
                        </span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) ($model->suggestion?->title ?? 'Sugestão indisponível')) ?></h2>
                    <p class="app-record-card__text">Ligada à proposta <?= Html::encode((string) ($model->suggestion?->proposal?->title ?? 'indisponível')) ?>.</p>
                    <div class="app-record-meta">
                        <span><strong>Data</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
                    </div>
                    <?php if ($model->suggestion !== null): ?>
                        <div class="app-record-card__actions">
                            <?= Html::a('Abrir proposta', ['/proposal/view', 'id' => $model->suggestion->proposal_id], ['class' => 'btn btn-primary app-btn']) ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination justify-content-center mt-4'],
        ]) ?>
    <?php endif; ?>
</div>
