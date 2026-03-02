<?php

/** @var yii\web\View $this */
/** @var app\models\Election $model */

use yii\bootstrap5\Html;

$this->title = 'Editar eleição';
$this->params['breadcrumbs'][] = ['label' => 'Eleições', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="election-update">
    <h1 class="h3"><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
