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
<div class="candidate-update">
    <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'userOptions' => $userOptions, 'electionOptions' => $electionOptions]) ?>
</div>
