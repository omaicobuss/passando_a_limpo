<?php

/** @var yii\web\View $this */
/** @var app\models\ElectionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Eleições';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="election-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('manageElection')): ?>
            <?= Html::a('Nova eleição', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin(['method' => 'get']); ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <?= $form->field($searchModel, 'title')->textInput(['placeholder' => 'Digite o titulo da eleicao'])->label('Título') ?>
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
            'title',
            'start_date',
            'end_date',
            [
                'class' => yii\grid\ActionColumn::class,
                'template' => '{view} {update} {delete}',
                'visibleButtons' => [
                    'update' => fn () => !Yii::$app->user->isGuest && Yii::$app->user->can('manageElection'),
                    'delete' => fn () => !Yii::$app->user->isGuest && Yii::$app->user->can('manageElection'),
                ],
            ],
        ],
    ]) ?>
</div>
