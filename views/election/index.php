<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Eleições';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="election-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->can('manageElection') || Yii::$app->user->identity->isAdmin())): ?>
            <?= Html::a('Nova eleição', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
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
                    'update' => fn () => !Yii::$app->user->isGuest && (Yii::$app->user->can('manageElection') || Yii::$app->user->identity->isAdmin()),
                    'delete' => fn () => !Yii::$app->user->isGuest && (Yii::$app->user->can('manageElection') || Yii::$app->user->identity->isAdmin()),
                ],
            ],
        ],
    ]) ?>
</div>
