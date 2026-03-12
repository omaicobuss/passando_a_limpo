<?php

/** @var app\models\Proposal $model */
/** @var array $candidateOptions */
/** @var array $electionOptions */

use app\models\Proposal;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$form = ActiveForm::begin(['id' => 'proposal-form']);
echo $form->field($model, 'title')->textInput(['maxlength' => true]);
echo $form->field($model, 'theme')->textInput(['maxlength' => true]);
echo $form->field($model, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Selecione']);
echo $form->field($model, 'candidate_id')->dropDownList($candidateOptions, ['prompt' => 'Selecione']);
echo $form->field($model, 'content')->textarea(['rows' => 8]);
echo $form->field($model, 'fulfillment_status')->dropDownList(Proposal::statusOptions());
echo Html::submitButton('Salvar', ['class' => 'btn btn-primary']);
ActiveForm::end();
