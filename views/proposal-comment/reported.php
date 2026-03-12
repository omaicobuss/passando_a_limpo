<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalComment;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = 'Comentários inapropriados';
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['/proposal/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-comment-reported">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($this->title) ?></h1>
        <span class="badge bg-warning text-dark"><?= (int) $dataProvider->getTotalCount() ?> pendentes</span>
    </div>

    <?php if ($dataProvider->getTotalCount() === 0): ?>
        <div class="alert alert-success mb-0">Nenhum comentário marcado como inapropriado no momento.</div>
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
                        $commentId = (int) $model->id;
                        $text = $model->isDeleted()
                            ? $model->getDisplayContent()
                            : StringHelper::truncateWords((string) $model->content, 24, '...');

                        $contentClass = $model->isDeleted() ? 'text-muted fst-italic mb-1' : 'mb-1';

                        $details = Html::a(
                            'Ver contexto na proposta',
                            Url::to(['/proposal/view', 'id' => $model->proposal_id]),
                            ['class' => 'small']
                        );

                        return Html::tag('div', Html::encode($text), ['id' => 'reported-comment-text-' . $commentId, 'class' => $contentClass])
                            . $details;
                    },
                ],
                [
                    'label' => 'Autor',
                    'value' => static fn (ProposalComment $model): string => $model->user->username ?? 'Usuário',
                ],
                [
                    'label' => 'Denúncias',
                    'value' => static fn (ProposalComment $model): int => (int) $model->getAttribute('report_count'),
                    'contentOptions' => ['style' => 'width: 110px;'],
                ],
                [
                    'label' => 'Última marcação',
                    'value' => static function (ProposalComment $model): string {
                        $timestamp = (int) $model->getAttribute('last_reported_at');
                        return $timestamp > 0
                            ? Yii::$app->formatter->asDatetime($timestamp, 'php:d/m/Y H:i')
                            : '-';
                    },
                    'contentOptions' => ['style' => 'width: 170px;'],
                ],
                [
                    'label' => 'Moderação',
                    'format' => 'raw',
                    'value' => static function (ProposalComment $model): string {
                        $commentId = (int) $model->id;
                        $csrfParam = Yii::$app->request->csrfParam;
                        $csrfToken = Yii::$app->request->csrfToken;

                        $deleteAction = $model->isDeleted()
                            ? Html::button('Já excluído', ['class' => 'btn btn-sm btn-outline-secondary', 'disabled' => true])
                            : Html::beginForm(['/proposal-comment/delete', 'id' => $commentId], 'post', ['class' => 'd-inline me-1', 'id' => 'moderate-delete-comment-' . $commentId])
                                . Html::hiddenInput($csrfParam, $csrfToken)
                                . Html::hiddenInput('back', 'reported')
                                . Html::submitButton('Excluir comentário', ['class' => 'btn btn-sm btn-outline-danger'])
                                . Html::endForm();

                        $keepAction = Html::beginForm(['/proposal-comment/resolve-report', 'id' => $commentId], 'post', ['class' => 'd-inline', 'id' => 'moderate-keep-comment-' . $commentId])
                            . Html::hiddenInput($csrfParam, $csrfToken)
                            . Html::submitButton('Manter comentário', ['class' => 'btn btn-sm btn-outline-primary'])
                            . Html::endForm();

                        return $deleteAction . $keepAction;
                    },
                    'contentOptions' => ['style' => 'width: 250px;'],
                ],
            ],
        ]) ?>
    <?php endif; ?>
</div>
