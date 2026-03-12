<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalSuggestionVote;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Meus votos em sugestões';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-suggestion-votes">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não votou em nenhuma sugestão.</div>
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
                    'label' => 'Sugestão',
                    'format' => 'raw',
                    'value' => static function (ProposalSuggestionVote $model): string {
                        $suggestion = $model->suggestion;
                        if ($suggestion === null) {
                            return '<span class="text-muted">—</span>';
                        }
                        return Html::a(
                            Html::encode((string) $suggestion->title),
                            Url::to(['/proposal/view', 'id' => $suggestion->proposal_id])
                        );
                    },
                ],
                [
                    'label' => 'Proposta',
                    'value' => static function (ProposalSuggestionVote $model): string {
                        return (string) ($model->suggestion?->proposal?->title ?? '—');
                    },
                ],
                [
                    'label' => 'Voto',
                    'format' => 'raw',
                    'value' => static function (ProposalSuggestionVote $model): string {
                        if ((int) $model->value > 0) {
                            return '<span class="badge bg-success">👍 Positivo</span>';
                        }
                        return '<span class="badge bg-danger">👎 Negativo</span>';
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Data',
                    'value' => static function (ProposalSuggestionVote $model): string {
                        return date('d/m/Y H:i', (int) $model->created_at);
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
