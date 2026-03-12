<?php

/** @var yii\web\View $this */
/** @var app\models\Election $model */

use yii\bootstrap5\Html;

$this->title = 'Nova eleição';
$this->params['breadcrumbs'][] = ['label' => 'Eleições', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="election-create entity-editor-page">
    <section class="editor-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Gestão eleitoral</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Estruture uma nova disputa com cronograma claro, descrição objetiva e período bem definido para participação pública.</p>
            </div>
            <div class="col-xl-4">
                <div class="editor-hero__meta">
                    <span><strong>Modo</strong> Criação</span>
                    <span><strong>Destino</strong> Lista de eleições</span>
                    <span><strong>Ação</strong> Publicar novo ciclo</span>
                </div>
            </div>
        </div>
    </section>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
