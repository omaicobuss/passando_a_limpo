<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Proposal;
use app\models\ProposalStatusUpdate;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = 'Minhas atualizações de status';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-status-updates">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não registrou nenhuma atualização de status.</div>
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
                    'label' => 'Proposta',
                    'format' => 'raw',
                    'value' => static function (ProposalStatusUpdate $model): string {
                        $proposal = $model->proposal;
                        if ($proposal === null) {
                            return '<span class="text-muted">—</span>';
                        }
                        return Html::a(
                            Html::encode((string) $proposal->title),
                            Url::to(['/proposal/view', 'id' => $proposal->id])
                        );
                    },
                ],
                [
                    'label' => 'Status registrado',
                    'format' => 'raw',
                    'value' => static function (ProposalStatusUpdate $model): string {
                        $options = Proposal::statusOptions();
                        $label = $options[$model->status] ?? $model->status;
                        return Html::encode((string) $label);
                    },
                ],
                [
                    'label' => 'Descrição',
                    'value' => static function (ProposalStatusUpdate $model): string {
                        return StringHelper::truncateWords((string) $model->description, 20, '...');
                    },
                ],
                [
                    'attribute' => 'update_date',
                    'label' => 'Data da atualização',
                    'value' => static function (ProposalStatusUpdate $model): string {
                        return date('d/m/Y', strtotime((string) $model->update_date));
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
