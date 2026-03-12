<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Proposal;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Minhas propostas publicadas';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-proposals">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não publicou nenhuma proposta.</div>
    <?php else: ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'tableOptions' => ['class' => 'table table-striped table-bordered align-middle'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'label' => '#',
                    'contentOptions' => ['style' => 'width: 70px;'],
                ],
                [
                    'label' => 'Título',
                    'format' => 'raw',
                    'value' => static function (Proposal $model): string {
                        return Html::a(
                            Html::encode((string) $model->title),
                            Url::to(['/proposal/view', 'id' => $model->id])
                        );
                    },
                ],
                [
                    'label' => 'Tema',
                    'value' => static function (Proposal $model): string {
                        return (string) ($model->theme ?? '—');
                    },
                ],
                [
                    'label' => 'Eleição',
                    'value' => static function (Proposal $model): string {
                        return (string) ($model->election?->title ?? '—');
                    },
                ],
                [
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => static function (Proposal $model): string {
                        $options = Proposal::statusOptions();
                        $label = $options[$model->fulfillment_status] ?? $model->fulfillment_status;
                        return Html::encode((string) $label);
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Publicada em',
                    'value' => static function (Proposal $model): string {
                        return date('d/m/Y', (int) $model->created_at);
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
