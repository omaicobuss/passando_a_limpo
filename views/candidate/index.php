<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\GridView;

$this->title = 'Candidatos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= Html::a('Novo candidato', ['create'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
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
                    'update' => function ($model) {
                        if (Yii::$app->user->isGuest) {
                            return false;
                        }
                        $user = Yii::$app->user->identity;
                        return $user->isAdmin() || (int) $model->user_id === (int) $user->id;
                    },
                    'delete' => function ($model) {
                        if (Yii::$app->user->isGuest) {
                            return false;
                        }
                        $user = Yii::$app->user->identity;
                        return $user->isAdmin() || (int) $model->user_id === (int) $user->id;
                    },
                ],
            ],
        ],
    ]) ?>
</div>
