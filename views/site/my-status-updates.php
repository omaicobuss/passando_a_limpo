<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Proposal;
use app\models\ProposalStatusUpdate;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;
use yii\widgets\LinkPager;

$this->title = 'Minhas atualizações de status';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-status-updates app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Acompanhamento pós-eleição</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Concentre em um só lugar o histórico de atualizações de execução que você registrou nas propostas.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>atualização(ões) publicadas</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não registrou nenhuma atualização de status.</div>
    <?php else: ?>
        <div class="app-record-stack app-record-stack--timeline">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalStatusUpdate $model */ ?>
                <?php $statusLabel = Proposal::statusOptions()[$model->status] ?? $model->status; ?>
                <article class="app-record-card app-record-card--timeline">
                    <div class="app-record-card__header">
                        <span class="app-record-chip app-record-chip--soft"><?= Html::encode((string) $statusLabel) ?></span>
                        <span class="app-record-card__id"><?= date('d/m/Y', strtotime((string) $model->update_date)) ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) ($model->proposal?->title ?? 'Proposta indisponível')) ?></h2>
                    <p class="app-record-card__text"><?= Html::encode(StringHelper::truncateWords((string) $model->description, 28, '...')) ?></p>
                    <div class="app-record-meta">
                        <span><strong>Registrada em</strong> <?= date('d/m/Y H:i', (int) $model->created_at) ?></span>
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
