<?php

/** @var yii\web\View $this */
/** @var app\models\ProposalSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $candidateOptions */
/** @var array $electionOptions */

use app\models\Proposal;
use app\models\ProposalSearch;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\widgets\ListView;

$this->title = 'Propostas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-index app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Catálogo de compromissos</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Explore propostas lançadas, compare níveis de comprometimento e acompanhe o score público de cada iniciativa.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>proposta(s) encontrada(s)</span>
                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('candidate')): ?>
                        <div class="app-page-metric__actions">
                            <?= Html::a('Nova proposta', ['create'], ['class' => 'btn btn-primary app-btn']) ?>
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
                <div class="col-xl-2 col-md-4"><?= $form->field($searchModel, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Todas'])->label('Eleição') ?></div>
                <div class="col-xl-3 col-md-4"><?= $form->field($searchModel, 'candidate_id')->dropDownList($candidateOptions, ['prompt' => 'Todos'])->label('Candidato') ?></div>
                <div class="col-xl-2 col-md-4"><?= $form->field($searchModel, 'theme')->textInput(['placeholder' => 'Ex: Saúde'])->label('Tema') ?></div>
                <div class="col-xl-2 col-md-6"><?= $form->field($searchModel, 'fulfillment_status')->dropDownList(Proposal::statusOptions(), ['prompt' => 'Todos'])->label('Status') ?></div>
                <div class="col-xl-3 col-md-6 d-flex gap-2 pb-3">
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary app-btn flex-grow-1']) ?>
                    <?= Html::a('Limpar', ['index'], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'app-record-grid app-record-grid--compact'],
        'itemOptions' => ['tag' => false],
        'layout' => "{items}\n<div class=\"col-12 mt-4 d-flex justify-content-center\">{pager}</div>",
        'emptyText' => '<div class="app-empty-state col-12"><h2 class="h5 mb-2">Nenhuma proposta encontrada</h2><p class="mb-0">Ajuste os filtros ou aguarde novas publicações dos candidatos.</p></div>',
        'itemView' => function (Proposal $model) {
            $statusLabel = Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status;

            $actions = '<a class="btn btn-primary app-btn" href="' . yii\helpers\Url::to(['view', 'id' => $model->id]) . '">Abrir proposta</a>';

            if (!Yii::$app->user->isGuest
                && Yii::$app->user->can('updateOwnProposal', ['proposal' => $model])
                && !$model->isEditLockedByElectionDeadline()) {
                $actions .= ' <a class="btn btn-outline-secondary app-btn app-btn--ghost" href="' . yii\helpers\Url::to(['update', 'id' => $model->id]) . '">Editar</a>';
            }

            return '<article class="app-record-card app-record-card--proposal">'
                . '<div class="app-record-card__header">'
                . '<span class="app-record-chip app-record-chip--soft">' . Html::encode($statusLabel) . '</span>'
                . '<span class="home-score-chip">Score ' . (int) $model->score . '</span>'
                . '</div>'
                . '<h2 class="app-record-card__title">' . Html::encode($model->title) . '</h2>'
                . '<p class="app-record-card__text">' . Html::encode(mb_strimwidth(strip_tags((string) $model->content), 0, 150, '...')) . '</p>'
                . '<div class="app-record-meta">'
                . '<span><strong>Tema</strong> ' . Html::encode((string) ($model->theme ?: 'Tema geral')) . '</span>'
                . '<span><strong>Candidato</strong> ' . Html::encode((string) ($model->candidate->display_name ?? '-')) . '</span>'
                . '</div>'
                . '<div class="app-record-card__actions">' . $actions . '</div>'
                . '</article>';
        },
    ]) ?>
</div>
