<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\Candidate;
use yii\bootstrap5\Html;
use yii\widgets\LinkPager;

$this->title = 'Meus perfis de candidato';
$this->params['breadcrumbs'][] = ['label' => 'Minha Conta', 'url' => ['/site/my-account']];
$this->params['breadcrumbs'][] = $this->title;

$models = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
?>
<div class="site-my-candidates app-account-collection">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Minha trajetória pública</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Veja em quais eleições você está registrado como candidato e abra cada perfil com um clique.</p>
            </div>
            <div class="col-lg-4">
                <div class="app-page-metric">
                    <strong><?= (int) $dataProvider->getTotalCount() ?></strong>
                    <span>perfil(is) vinculado(s)</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (empty($models)): ?>
        <div class="alert alert-info mb-0">Você ainda não possui perfis de candidato.</div>
    <?php else: ?>
        <div class="app-record-grid app-record-grid--compact">
            <?php foreach ($models as $model): ?>
                <?php /** @var Candidate $model */ ?>
                <article class="app-record-card app-record-card--candidate">
                    <div class="app-record-card__header">
                        <span class="app-record-chip app-record-chip--soft"><?= Html::encode((string) ($model->election->title ?? 'Sem eleição')) ?></span>
                        <span class="app-record-card__id">#<?= (int) $model->id ?></span>
                    </div>
                    <h2 class="app-record-card__title"><?= Html::encode((string) $model->display_name) ?></h2>
                    <p class="app-record-card__text"><?= Html::encode(mb_strimwidth(trim((string) $model->bio) ?: 'Perfil de candidatura disponível para consulta pública.', 0, 140, '...')) ?></p>
                    <div class="app-record-meta">
                        <span><strong>Eleição</strong> <?= Html::encode((string) ($model->election->title ?? '-')) ?></span>
                        <span><strong>Criado em</strong> <?= date('d/m/Y', (int) $model->created_at) ?></span>
                    </div>
                    <div class="app-record-card__actions">
                        <?= Html::a('Ver perfil', ['/candidate/view', 'id' => $model->id], ['class' => 'btn btn-primary app-btn']) ?>
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
