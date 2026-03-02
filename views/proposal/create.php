<?php

/** @var yii\web\View $this */
/** @var app\models\Proposal $model */
/** @var array $candidateOptions */
/** @var array $electionOptions */

use yii\bootstrap5\Html;

$this->title = 'Nova proposta';
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-create">
    <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'candidateOptions' => $candidateOptions,
        'electionOptions' => $electionOptions,
    ]) ?>
</div>
