<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Candidate;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Meus perfis de candidato';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-my-candidates">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-secondary rounded-pill"><?= (int) $dataProvider->getTotalCount() ?> registro(s)</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-info mb-0">Você ainda não possui perfis de candidato.</div>
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
                    'label' => 'Nome de exibição',
                    'format' => 'raw',
                    'value' => static function (Candidate $model): string {
                        return Html::a(
                            Html::encode((string) $model->display_name),
                            Url::to(['/candidate/view', 'id' => $model->id])
                        );
                    },
                ],
                [
                    'label' => 'Eleição',
                    'format' => 'raw',
                    'value' => static function (Candidate $model): string {
                        $election = $model->election;
                        if ($election === null) {
                            return '<span class="text-muted">—</span>';
                        }
                        return Html::encode((string) $election->title);
                    },
                ],
                [
                    'attribute' => 'created_at',
                    'label' => 'Criado em',
                    'format' => 'raw',
                    'value' => static function (Candidate $model): string {
                        return Html::encode(date('d/m/Y', (int) $model->created_at));
                    },
                ],
            ],
        ]); ?>
    <?php endif; ?>
</div>
