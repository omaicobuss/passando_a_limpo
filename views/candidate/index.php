<?php

/** @var yii\web\View $this */
/** @var app\models\CandidateSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,string> $electionOptions */

use app\models\Candidate;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Candidatos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('candidate')): ?>
            <?= Html::a('Novo candidato', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <?= $form->field($searchModel, 'display_name')->textInput(['placeholder' => 'Nome do candidato'])->label('Candidato') ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($searchModel, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Todas'])->label('Eleição') ?>
                </div>
                <div class="col-md-4 d-flex gap-2 pb-3">
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Limpar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            'id',
            'display_name',
            [
                'label' => 'Usuário',
                'value' => fn ($model) => $model->user->username ?? '-',
            ],
            [
                'label' => 'Eleição',
                'value' => fn ($model) => $model->election->title ?? '-',
            ],
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'visibleButtons' => [
                    'update' => function (Candidate $model) {
                        if (Yii::$app->user->isGuest) {
                            return false;
                        }
                        return Yii::$app->user->can('manageCandidate', ['candidate' => $model]);
                    },
                    'delete' => function (Candidate $model) {
                        if (Yii::$app->user->isGuest) {
                            return false;
                        }
                        return Yii::$app->user->can('manageCandidate', ['candidate' => $model]);
                    },
                ],
            ],
        ],
    ]) ?>
</div>
