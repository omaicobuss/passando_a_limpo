<?php

/** @var app\models\Election $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$submitLabel = $model->isNewRecord ? 'Publicar eleição' : 'Salvar alterações';
?>
<div class="editor-shell">
	<div class="row g-4 align-items-start">
		<div class="col-xl-8">
			<section class="editor-form-card">
				<div class="editor-form-card__header">
					<span class="app-section-eyebrow">Dados principais</span>
					<h2 class="h4 mt-3 mb-2">Configuração do ciclo eleitoral</h2>
					<p class="mb-0">Preencha título, contexto e período oficial para liberar o acompanhamento público de candidatos e propostas.</p>
				</div>

				<?php $form = ActiveForm::begin(['options' => ['class' => 'editor-form']]); ?>

				<div class="editor-fieldset mt-4">
					<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>
				</div>

				<div class="row g-3 mt-1">
					<div class="col-md-6">
						<?= $form->field($model, 'start_date')->input('date') ?>
					</div>
					<div class="col-md-6">
						<?= $form->field($model, 'end_date')->input('date') ?>
					</div>
				</div>

				<div class="editor-submit-row mt-4">
					<?= Html::submitButton($submitLabel, ['class' => 'btn btn-primary app-btn']) ?>
					<?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
				</div>

				<?php ActiveForm::end(); ?>
			</section>
		</div>

		<div class="col-xl-4">
			<aside class="editor-sidebar-card">
				<span class="app-section-eyebrow">Checklist</span>
				<h3 class="h5 mt-3 mb-3">Antes de salvar</h3>
				<ul class="editor-checklist">
					<li>Use um título curto e reconhecível.</li>
					<li>Defina datas coerentes com o calendário oficial.</li>
					<li>Descreva escopo e regras da eleição.</li>
				</ul>
			</aside>

			<aside class="editor-sidebar-card editor-sidebar-card--accent mt-3">
				<span class="app-section-eyebrow">Impacto no sistema</span>
				<p class="mb-0">Esta configuração será usada no vínculo de candidatos e propostas, além de influenciar restrições por prazo.</p>
			</aside>
		</div>
	</div>
</div>
