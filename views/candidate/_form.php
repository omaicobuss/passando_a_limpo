<?php

/** @var app\models\Candidate $model */
/** @var array $userOptions */
/** @var array $electionOptions */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$submitLabel = $model->isNewRecord ? 'Cadastrar candidato' : 'Salvar alterações';
?>
<div class="editor-shell">
	<div class="row g-4 align-items-start">
		<div class="col-xl-8">
			<section class="editor-form-card">
				<div class="editor-form-card__header">
					<span class="app-section-eyebrow">Perfil público</span>
					<h2 class="h4 mt-3 mb-2">Dados da candidatura</h2>
					<p class="mb-0">Associe o candidato a um usuário real e a uma eleição, depois registre nome de exibição e biografia oficial.</p>
				</div>

				<?php $form = ActiveForm::begin(['options' => ['class' => 'editor-form']]); ?>

				<div class="row g-3 mt-1">
					<div class="col-md-6">
						<?= $form->field($model, 'user_id')->dropDownList($userOptions, ['prompt' => 'Selecione']) ?>
					</div>
					<div class="col-md-6">
						<?= $form->field($model, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Selecione']) ?>
					</div>
				</div>

				<div class="editor-fieldset mt-2">
					<?= $form->field($model, 'display_name')->textInput(['maxlength' => true]) ?>
					<?= $form->field($model, 'bio')->textarea(['rows' => 6]) ?>
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
				<h3 class="h5 mt-3 mb-3">Boas práticas</h3>
				<ul class="editor-checklist">
					<li>Nome de exibição deve ser claro e único.</li>
					<li>Biografia deve explicar foco de atuação.</li>
					<li>Revise se o usuário e a eleição estão corretos.</li>
				</ul>
			</aside>

			<aside class="editor-sidebar-card editor-sidebar-card--accent mt-3">
				<span class="app-section-eyebrow">Visibilidade</span>
				<p class="mb-0">As informações deste formulário aparecem em listagens públicas e no contexto das propostas ligadas ao candidato.</p>
			</aside>
		</div>
	</div>
</div>
