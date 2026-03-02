<?php

/** @var app\models\Candidate $model */
/** @var array $userOptions */
/** @var array $electionOptions */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$form = ActiveForm::begin();
echo $form->field($model, 'user_id')->dropDownList($userOptions, ['prompt' => 'Selecione']);
echo $form->field($model, 'election_id')->dropDownList($electionOptions, ['prompt' => 'Selecione']);
echo $form->field($model, 'display_name')->textInput(['maxlength' => true]);
echo $form->field($model, 'bio')->textarea(['rows' => 5]);
echo Html::submitButton('Salvar', ['class' => 'btn btn-primary']);
ActiveForm::end();
