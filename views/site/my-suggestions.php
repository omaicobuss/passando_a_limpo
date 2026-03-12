<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalSuggestion;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Minhas sugestões em propostas';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-suggestions">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não enviou nenhuma sugestão.</div>
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
                    'label' => 'Título da sugestão',
                    'format' => 'raw',
                    'value' => static function (ProposalSuggestion $model): string {
                        return Html::a(
                            Html::encode((string) $model->title),
                            Url::to(['/proposal/view', 'id' => $model->proposal_id])
                        );
                    },
                ],
                [
                    'label' => 'Proposta',
                    'value' => static function (ProposalSuggestion $model): string {
                        return (string) ($model->proposal?->title ?? '—');
                    },
                ],
                [
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => static function (ProposalSuggestion $model): string {
                        return match ($model->status) {
                            ProposalSuggestion::STATUS_APPROVED => '<span class="badge bg-success">Aprovada</span>',
                            ProposalSuggestion::STATUS_REJECTED => '<span class="badge bg-danger">Rejeitada</span>',
                            default => '<span class="badge bg-warning text-dark">Pendente</span>',
                        };
                    },
                ],
                [
                    'label' => 'Pontuação',
                    'value' => static function (ProposalSuggestion $model): int {
                        return $model->getScore();
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Data',
                    'value' => static function (ProposalSuggestion $model): string {
                        return date('d/m/Y H:i', (int) $model->created_at);
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
