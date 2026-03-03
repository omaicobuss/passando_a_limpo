<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\CandidateUpgradeRequest;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Solicitacoes de Candidatura';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-candidate-requests">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Analise os pedidos enviados por usuarios citizen para mudanca de perfil para candidate.</p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered align-middle'],
        'columns' => [
            'id',
            [
                'label' => 'Usuario',
                'value' => static fn (CandidateUpgradeRequest $model) => $model->user->username ?? '#'.$model->user_id,
            ],
            [
                'attribute' => 'status',
                'value' => static fn (CandidateUpgradeRequest $model) => $model->getStatusLabel(),
            ],
            [
                'label' => 'Comprovante',
                'format' => 'raw',
                'value' => static fn (CandidateUpgradeRequest $model) => Html::a('Baixar', ['site/candidate-request-document', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']),
            ],
            [
                'attribute' => 'message',
                'contentOptions' => ['style' => 'max-width:260px; white-space:normal;'],
            ],
            [
                'label' => 'Revisao',
                'value' => static function (CandidateUpgradeRequest $model): string {
                    if ($model->reviewed_by === null) {
                        return '-';
                    }
                    $reviewer = $model->reviewer->username ?? '#'.$model->reviewed_by;
                    $reviewedAt = $model->reviewed_at ? date('d/m/Y H:i', (int) $model->reviewed_at) : '-';
                    return $reviewer . ' em ' . $reviewedAt;
                },
            ],
            [
                'label' => 'Acao',
                'format' => 'raw',
                'value' => static function (CandidateUpgradeRequest $model): string {
                    if ($model->status !== CandidateUpgradeRequest::STATUS_PENDING) {
                        return Html::tag('small', Html::encode((string) $model->admin_notes ?: '-'), ['class' => 'text-muted']);
                    }

                    $approveForm = Html::beginForm(['site/candidate-request-review', 'id' => $model->id, 'decision' => 'approve'], 'post', ['class' => 'mb-2'])
                        . Html::textInput('admin_notes', '', ['class' => 'form-control form-control-sm mb-1', 'placeholder' => 'Observacao (opcional)'])
                        . Html::submitButton('Aprovar', ['class' => 'btn btn-sm btn-success'])
                        . Html::endForm();

                    $rejectForm = Html::beginForm(['site/candidate-request-review', 'id' => $model->id, 'decision' => 'reject'], 'post')
                        . Html::textInput('admin_notes', '', ['class' => 'form-control form-control-sm mb-1', 'placeholder' => 'Motivo da reprovacao'])
                        . Html::submitButton('Reprovar', ['class' => 'btn btn-sm btn-danger'])
                        . Html::endForm();

                    return $approveForm . $rejectForm;
                },
                'contentOptions' => ['style' => 'min-width:260px;'],
            ],
        ],
    ]); ?>
</div>
