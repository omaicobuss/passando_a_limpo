<?php

/** @var yii\web\View $this */
/** @var app\models\Proposal $model */
/** @var array $candidateOptions */
/** @var array $electionOptions */

use yii\bootstrap5\Html;

$this->title = 'Editar proposta';
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-update entity-editor-page">
    <section class="editor-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Gestão de propostas</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Atualize conteúdo e status para manter transparência no acompanhamento e coerência com o progresso pós-eleição.</p>
            </div>
            <div class="col-xl-4">
                <div class="editor-hero__meta">
                    <span><strong>Modo</strong> Edição</span>
                    <span><strong>Proposta</strong> <?= Html::encode((string) $model->title) ?></span>
                    <span><strong>Ação</strong> Salvar revisão</span>
                </div>
            </div>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'candidateOptions' => $candidateOptions,
        'electionOptions' => $electionOptions,
    ]) ?>
</div>
