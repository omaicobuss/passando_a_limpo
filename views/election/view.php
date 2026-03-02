<?php

/** @var yii\web\View $this */
/** @var app\models\Election $model */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Eleições', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="election-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->can('manageElection') || Yii::$app->user->identity->isAdmin())): ?>
                <?= Html::a('Nova eleição', ['create'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
            <?= Html::a('Ver propostas', ['/proposal/index', 'ProposalSearch[election_id]' => $model->id], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'description:ntext',
            'start_date',
            'end_date',
        ],
    ]) ?>
</div>
