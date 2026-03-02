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
<div class="proposal-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isCandidate()): ?>
            <?= Html::a('Nova proposta', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="row g-2">
                <div class="col-md-3"><?= $form->field($searchModel, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Todas'])->label('Eleição') ?></div>
                <div class="col-md-3"><?= $form->field($searchModel, 'candidate_id')->dropDownList($candidateOptions, ['prompt' => 'Todos'])->label('Candidato') ?></div>
                <div class="col-md-2"><?= $form->field($searchModel, 'theme')->textInput()->label('Tema') ?></div>
                <div class="col-md-2"><?= $form->field($searchModel, 'fulfillment_status')->dropDownList(Proposal::statusOptions(), ['prompt' => 'Todos'])->label('Status') ?></div>
                <div class="col-md-2"><?= $form->field($searchModel, 'sort')->dropDownList(ProposalSearch::sortOptions())->label('Ordenação') ?></div>
            </div>
            <div class="d-flex gap-2">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Limpar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'mb-3'],
        'layout' => '{items}{pager}',
        'itemView' => function (Proposal $model) {
            return '<div class="card"><div class="card-body">'
                . '<h2 class="h5 mb-1">' . Html::encode($model->title) . '</h2>'
                . '<p class="text-muted mb-1">' . Html::encode((string) $model->theme) . ' · ' . Html::encode($model->candidate->display_name ?? '-') . '</p>'
                . '<p class="mb-2">Score: <strong>' . (int) $model->score . '</strong> | Status: ' . Html::encode(Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status) . '</p>'
                . '<a class="btn btn-sm btn-primary" href="' . yii\helpers\Url::to(['view', 'id' => $model->id]) . '">Abrir</a>'
                . '</div></div>';
        },
    ]) ?>
</div>
