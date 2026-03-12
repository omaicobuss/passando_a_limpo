<?php

/** @var yii\web\View $this */
/** @var app\models\ElectionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Election;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Eleições';
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$canManageElection = !Yii::$app->user->isGuest && Yii::$app->user->can('manageElection');
?>
<div class="election-index app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Catálogo eleitoral</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Explore ciclos eleitorais, datas-chave e acessos rápidos para leitura e administração do calendário público.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>registro(s) encontrados</span>
                    <?php if ($canManageElection): ?>
                        <div class="app-page-metric__actions">
                            <?= Html::a('Nova eleição', ['create'], ['class' => 'btn btn-primary app-btn']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="card app-filter-card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <?= $form->field($searchModel, 'title')->textInput(['placeholder' => 'Digite o título da eleição'])->label('Título') ?>
                </div>
                <div class="col-lg-4 d-flex gap-2 pb-3">
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary app-btn']) ?>
                    <?= Html::a('Limpar', ['index'], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (empty($models)): ?>
        <div class="app-empty-state">
            <h2 class="h5 mb-2">Nenhuma eleição encontrada</h2>
            <p class="mb-0">Ajuste os filtros ou cadastre um novo processo eleitoral para iniciar o acompanhamento.</p>
        </div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var Election $model */ ?>
                <?php $isFinished = $model->hasFinished(); ?>
                <article class="app-record-card app-record-card--election">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $isFinished ? 'app-record-chip--muted' : 'app-record-chip--accent' ?>">
                            <?= $isFinished ? 'Encerrada' : 'Ativa' ?>
                        </span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode($model->title) ?></h2>
                    <p class="app-record-card__text">Período oficial de <?= Html::encode((string) $model->start_date) ?> até <?= Html::encode((string) $model->end_date) ?>.</p>
                    <div class="app-record-meta">
                        <span><strong>Início</strong> <?= Html::encode((string) $model->start_date) ?></span>
                        <span><strong>Término</strong> <?= Html::encode((string) $model->end_date) ?></span>
                    </div>
                    <div class="app-record-card__actions">
                        <?= Html::a('Ver detalhes', ['view', 'id' => $model->id], ['class' => 'btn btn-primary app-btn']) ?>
                        <?php if ($canManageElection): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                            <?= Html::a('Excluir', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-outline-danger app-btn app-btn--ghost',
                                'data-method' => 'post',
                                'data-confirm' => 'Tem certeza que deseja excluir esta eleição?',
                            ]) ?>
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
