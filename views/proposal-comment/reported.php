<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\ProposalComment;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Comentários inapropriados';
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['/proposal/index']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="proposal-comment-reported app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Fila de moderação</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Revise denúncias com contexto de proposta, histórico de marcações e decisões rápidas em uma interface de cards.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>comentário(s) pendente(s)</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-success mb-0">Nenhum comentário marcado como inapropriado no momento.</div>
    <?php else: ?>
        <div class="app-record-stack">
            <?php foreach ($models as $model): ?>
                <?php /** @var ProposalComment $model */ ?>
                <?php
                $commentId = (int) $model->id;
                $reportCount = (int) $model->getAttribute('report_count');
                $timestamp = (int) $model->getAttribute('last_reported_at');
                $text = $model->isDeleted()
                    ? $model->getDisplayContent()
                    : StringHelper::truncateWords((string) $model->content, 30, '...');
                ?>
                <article class="app-record-card app-record-card--admin">
                    <div class="app-record-card__header">
                        <span class="app-record-chip <?= $model->isDeleted() ? 'app-record-chip--muted' : 'app-record-chip--danger' ?>">
                            <?= $model->isDeleted() ? 'Já excluído' : 'Denunciado' ?>
                        </span>
                        <span class="app-record-card__id">#<?= $commentId ?></span>
                    </div>
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-7">
                            <div id="reported-comment-text-<?= $commentId ?>" class="app-record-card__text<?= $model->isDeleted() ? ' fst-italic' : '' ?>">
                                <?= Html::encode($text) ?>
                            </div>
                            <div class="app-record-meta mt-3">
                                <span><strong>Autor</strong> <?= Html::encode((string) ($model->user->username ?? 'Usuário')) ?></span>
                                <span><strong>Denúncias</strong> <?= $reportCount ?></span>
                                <span><strong>Última marcação</strong> <?= $timestamp > 0 ? Yii::$app->formatter->asDatetime($timestamp, 'php:d/m/Y H:i') : '-' ?></span>
                            </div>
                            <div class="mt-3">
                                <?= Html::a('Ver contexto na proposta', Url::to(['/proposal/view', 'id' => $model->proposal_id]), ['class' => 'home-inline-link']) ?>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="app-side-panel h-100">
                                <div class="app-admin-action-group">
                                    <?php if ($model->isDeleted()): ?>
                                        <?= Html::button('Já excluído', ['class' => 'btn btn-outline-secondary app-btn w-100', 'disabled' => true]) ?>
                                    <?php else: ?>
                                        <?= Html::beginForm(['/proposal-comment/delete', 'id' => $commentId], 'post', ['id' => 'moderate-delete-comment-' . $commentId, 'class' => 'app-admin-action-form']) ?>
                                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                        <?= Html::hiddenInput('back', 'reported') ?>
                                        <?= Html::submitButton('Excluir comentário', ['class' => 'btn btn-outline-danger app-btn w-100']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>

                                    <?= Html::beginForm(['/proposal-comment/resolve-report', 'id' => $commentId], 'post', ['id' => 'moderate-keep-comment-' . $commentId, 'class' => 'app-admin-action-form']) ?>
                                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                    <?= Html::submitButton('Manter comentário', ['class' => 'btn btn-outline-primary app-btn w-100']) ?>
                                    <?= Html::endForm() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination justify-content-center mt-4'],
        ]) ?>
    <?php endif; ?>
</div>
