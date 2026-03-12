<?php

/** @var app\models\Proposal $model */
/** @var array $candidateOptions */
/** @var array $electionOptions */

use app\models\Proposal;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$submitLabel = $model->isNewRecord ? 'Publicar proposta' : 'Salvar alterações';
?>
<div class="editor-shell">
	<div class="row g-4 align-items-start">
		<div class="col-xl-8">
			<section class="editor-form-card">
				<div class="editor-form-card__header">
					<span class="app-section-eyebrow">Núcleo da proposta</span>
					<h2 class="h4 mt-3 mb-2">Dados estratégicos</h2>
					<p class="mb-0">Defina estrutura, vínculo institucional e andamento para facilitar leitura pública e auditoria administrativa.</p>
				</div>

				<?php $form = ActiveForm::begin(['id' => 'proposal-form', 'options' => ['class' => 'editor-form']]); ?>

				<div class="editor-fieldset mt-4">
					<?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'theme')->textInput(['maxlength' => true]) ?>
				</div>

				<div class="row g-3 mt-1">
					<div class="col-md-6">
						<?= $form->field($model, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Selecione']) ?>
					</div>
					<div class="col-md-6">
						<?= $form->field($model, 'candidate_id')->dropDownList($candidateOptions, ['prompt' => 'Selecione']) ?>
					</div>
				</div>

				<div class="editor-fieldset mt-2">
					<?= $form->field($model, 'content')->textarea(['rows' => 10]) ?>
					<?= $form->field($model, 'fulfillment_status')->dropDownList(Proposal::statusOptions()) ?>
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
				<h3 class="h5 mt-3 mb-3">Consistência editorial</h3>
				<ul class="editor-checklist">
					<li>Use tema alinhado ao plano da candidatura.</li>
					<li>Descreva ações de forma objetiva e verificável.</li>
					<li>Mantenha status de execução atualizado.</li>
				</ul>
			</aside>

			<aside class="editor-sidebar-card editor-sidebar-card--accent mt-3">
				<span class="app-section-eyebrow">Governança</span>
				<p class="mb-0">Após o prazo da eleição, edições podem ser bloqueadas; registre avanços no acompanhamento pós-eleição.</p>
			</aside>
		</div>
	</div>
</div>
