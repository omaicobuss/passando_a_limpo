<?php

/** @var app\models\Election $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$form = ActiveForm::begin();
echo $form->field($model, 'title')->textInput(['maxlength' => true]);
echo $form->field($model, 'description')->textarea(['rows' => 4]);
echo $form->field($model, 'start_date')->input('date');
echo $form->field($model, 'end_date')->input('date');
echo Html::submitButton('Salvar', ['class' => 'btn btn-primary']);
ActiveForm::end();
