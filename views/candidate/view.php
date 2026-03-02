<?php

/** @var yii\web\View $this */
/** @var app\models\Candidate $model */
/** @var app\models\Proposal[] $proposals */

use yii\bootstrap5\Html;

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Candidatos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($model->display_name) ?></h1>
        <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->identity->isAdmin() || (int) Yii::$app->user->id === (int) $model->user_id)): ?>
            <?= Html::a('Editar perfil', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
        <?php endif; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-2"><strong>Usuário:</strong> <?= Html::encode($model->user->username ?? '-') ?></p>
            <p class="mb-2"><strong>Eleição:</strong> <?= Html::encode($model->election->title ?? '-') ?></p>
            <p class="mb-0"><?= nl2br(Html::encode((string) $model->bio)) ?></p>
        </div>
    </div>

    <h2 class="h5">Propostas</h2>
    <div class="list-group">
        <?php foreach ($proposals as $proposal): ?>
            <a href="<?= yii\helpers\Url::to(['/proposal/view', 'id' => $proposal->id]) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                <span><?= Html::encode($proposal->title) ?></span>
                <span class="badge bg-primary rounded-pill"><?= (int) $proposal->score ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
