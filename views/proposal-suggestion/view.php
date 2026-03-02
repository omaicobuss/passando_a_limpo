<?php

/** @var yii\web\View $this */
/** @var app\models\ProposalSuggestion $model */

use app\models\ProposalSuggestion;
use yii\bootstrap5\Html;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['/proposal/index']];
$this->params['breadcrumbs'][] = ['label' => $model->proposal->title ?? 'Proposta', 'url' => ['/proposal/view', 'id' => $model->proposal_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-suggestion-view">
    <h1 class="h3"><?= Html::encode($model->title) ?></h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Status:</strong> <?= Html::encode(ProposalSuggestion::statusOptions()[$model->status] ?? $model->status) ?></p>
            <p><strong>Autor:</strong> <?= Html::encode($model->user->username ?? '-') ?></p>
            <p><?= nl2br(Html::encode($model->content)) ?></p>
            <p class="mb-0"><strong>Score:</strong> <?= $model->getScore() ?></p>
        </div>
    </div>
</div>
