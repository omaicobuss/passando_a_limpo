<?php

/** @var yii\web\View $this */
/** @var app\models\Candidate $model */
/** @var array $userOptions */
/** @var array $electionOptions */

use yii\bootstrap5\Html;

$this->title = 'Novo candidato';
$this->params['breadcrumbs'][] = ['label' => 'Candidatos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-create entity-editor-page">
    <section class="editor-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Gestão de candidaturas</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Cadastre o perfil público de um candidato e conecte o registro ao usuário e à eleição corretos.</p>
            </div>
            <div class="col-xl-4">
                <div class="editor-hero__meta">
                    <span><strong>Modo</strong> Criação</span>
                    <span><strong>Destino</strong> Lista de candidatos</span>
                    <span><strong>Ação</strong> Publicar perfil</span>
                </div>
            </div>
        </div>
    </section>

    <?= $this->render('_form', ['model' => $model, 'userOptions' => $userOptions, 'electionOptions' => $electionOptions]) ?>
</div>
