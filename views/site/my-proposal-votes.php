<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalVote;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Meus votos em propostas';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-proposal-votes app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Histórico de votos</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Revise em quais propostas você já votou e o sentido de cada escolha registrada.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>voto(s) em propostas</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não votou em nenhuma proposta.</div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalVote $model */ ?>
                <?php $isPositive = (int) $model->value > 0; ?>
                <article class="app-record-card app-record-card--vote">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $isPositive ? 'app-record-chip--success' : 'app-record-chip--danger' ?>">
                            <?= $isPositive ? 'Voto positivo' : 'Voto negativo' ?>
                        </span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) ($model->proposal?->title ?? 'Proposta indisponível')) ?></h2>
                    <p class="app-record-card__text">Seu voto foi registrado como <?= $isPositive ? 'apoio' : 'rejeição' ?> nesta proposta.</p>
                    <div class="app-record-meta">
                        <span><strong>Data</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
                    </div>
                    <?php if ($model->proposal !== null): ?>
                        <div class="app-record-card__actions">
                            <?= Html::a('Abrir proposta', ['/proposal/view', 'id' => $model->proposal->id], ['class' => 'btn btn-primary app-btn']) ?>
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
