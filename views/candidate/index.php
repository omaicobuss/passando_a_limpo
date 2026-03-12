<?php

/** @var yii\web\View $this */
/** @var app\models\CandidateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,string> $electionOptions */

use app\models\Candidate;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Candidatos';
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$canCreateCandidate = !Yii::$app->user->isGuest && Yii::$app->user->can('candidate');
?>
<div class="candidate-index app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Representação pública</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Navegue pelos perfis, compare participação em eleições e abra cada candidatura em uma visão mais editorial e direta.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>perfil(is) encontrado(s)</span>
                    <?php if ($canCreateCandidate): ?>
                        <div class="app-page-metric__actions">
                            <?= Html::a('Novo candidato', ['create'], ['class' => 'btn btn-primary app-btn']) ?>
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
                <div class="col-lg-4">
                    <?= $form->field($searchModel, 'display_name')->textInput(['placeholder' => 'Nome do candidato'])->label('Candidato') ?>
                </div>
                <div class="col-lg-4">
                    <?= $form->field($searchModel, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Todas'])->label('Eleição') ?>
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
            <h2 class="h5 mb-2">Nenhum candidato encontrado</h2>
            <p class="mb-0">Refine os filtros ou publique um novo perfil quando houver elegibilidade para candidatura.</p>
        </div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var Candidate $model */ ?>
                <?php $canManageCandidate = !Yii::$app->user->isGuest && Yii::$app->user->can('manageCandidate', ['candidate' => $model]); ?>
                <article class="app-record-card app-record-card--candidate">
                    <div class="app-record-card__header">
                        <span class="app-record-chip app-record-chip--soft"><?= Html::encode((string) ($model->election->title ?? 'Sem eleição')) ?></span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode($model->display_name) ?></h2>
                    <p class="app-record-card__text">
                        <?= Html::encode(mb_strimwidth(trim((string) $model->bio) ?: 'Perfil cadastrado para acompanhamento público de propostas e participação eleitoral.', 0, 150, '...')) ?>
                    </p>
                    <div class="app-record-meta">
                        <span><strong>Usuário</strong> <?= Html::encode((string) ($model->user->username ?? '-')) ?></span>
                        <span><strong>Eleição</strong> <?= Html::encode((string) ($model->election->title ?? '-')) ?></span>
                    </div>
                    <div class="app-record-card__actions">
                        <?= Html::a('Ver perfil', ['view', 'id' => $model->id], ['class' => 'btn btn-primary app-btn']) ?>
                        <?php if ($canManageCandidate): ?>
                            <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                            <?= Html::a('Excluir', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-outline-danger app-btn app-btn--ghost',
                                'data-method' => 'post',
                                'data-confirm' => 'Tem certeza que deseja excluir este candidato?',
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
