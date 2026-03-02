<?php

/** @var yii\web\View $this */
/** @var array $stats */

use yii\bootstrap5\Html;

$this->title = 'Painel do Candidato';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-panel-index">
    <h1 class="h3 mb-3"><?= Html::encode($this->title) ?></h1>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Total de propostas</div><div class="display-6"><?= (int) $stats['totalProposals'] ?></div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Média de votos</div><div class="display-6"><?= number_format((float) $stats['avgScore'], 2, ',', '.') ?></div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Sugestões pendentes</div><div class="display-6"><?= (int) $stats['pendingSuggestions'] ?></div></div></div></div>
    </div>

    <div class="d-flex gap-2 mb-4">
        <?= Html::a('Gerenciar propostas', ['/proposal/index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Nova proposta', ['/proposal/create'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Comentários recentes</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($stats['recentComments'] as $comment): ?>
                        <li class="list-group-item">
                            <a href="<?= yii\helpers\Url::to(['/proposal/view', 'id' => $comment->proposal_id]) ?>">
                                <?= Html::encode(mb_strimwidth($comment->content, 0, 80, '...')) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($stats['recentComments'])): ?>
                        <li class="list-group-item text-muted">Sem comentários recentes.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Minhas propostas</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($stats['myProposals'] as $proposal): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?= yii\helpers\Url::to(['/proposal/view', 'id' => $proposal->id]) ?>"><?= Html::encode($proposal->title) ?></a>
                            <span class="badge bg-primary"><?= (int) $proposal->score ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($stats['myProposals'])): ?>
                        <li class="list-group-item text-muted">Sem propostas cadastradas.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
