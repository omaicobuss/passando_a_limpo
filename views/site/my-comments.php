<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalComment;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = 'Meus comentários';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-comments">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não publicou nenhum comentário.</div>
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
                    'label' => 'Comentário',
                    'format' => 'raw',
                    'value' => static function (ProposalComment $model): string {
                        $text = $model->isDeleted()
                            ? '<span class="text-muted fst-italic">' . Html::encode($model->getDisplayContent()) . '</span>'
                            : Html::encode(StringHelper::truncateWords((string) $model->content, 20, '...'));
                        $link = Html::a(
                            'Ver proposta',
                            Url::to(['/proposal/view', 'id' => $model->proposal_id]),
                            ['class' => 'small ms-2']
                        );
                        return $text . $link;
                    },
                ],
                [
                    'label' => 'Proposta',
                    'value' => static function (ProposalComment $model): string {
                        return (string) ($model->proposal?->title ?? '—');
                    },
                ],
                [
                    'label' => 'Status',
                    'format' => 'raw',
                    'value' => static function (ProposalComment $model): string {
                        if ($model->isDeleted()) {
                            return '<span class="badge bg-danger">Excluído</span>';
                        }
                        if ($model->is_inappropriate) {
                            return '<span class="badge bg-warning text-dark">Marcado inapropriado</span>';
                        }
                        return '<span class="badge bg-success">Ativo</span>';
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Data',
                    'value' => static function (ProposalComment $model): string {
                        return date('d/m/Y H:i', (int) $model->created_at);
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
