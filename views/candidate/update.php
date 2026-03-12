<?php

/** @var yii\web\View $this */
/** @var app\models\Candidate $model */
/** @var array $userOptions */
/** @var array $electionOptions */

use yii\bootstrap5\Html;

$this->title = 'Editar candidato';
$this->params['breadcrumbs'][] = ['label' => 'Candidatos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-update entity-editor-page">
    <section class="editor-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Gestão de candidaturas</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Revise a apresentação do candidato para garantir dados consistentes ao longo da campanha e do pós-eleição.</p>
            </div>
            <div class="col-xl-4">
                <div class="editor-hero__meta">
                    <span><strong>Modo</strong> Edição</span>
                    <span><strong>Candidato</strong> <?= Html::encode((string) $model->display_name) ?></span>
                    <span><strong>Ação</strong> Atualizar perfil</span>
                </div>
            </div>
        </div>
    </section>

    <?= $this->render('_form', ['model' => $model, 'userOptions' => $userOptions, 'electionOptions' => $electionOptions]) ?>
</div>
