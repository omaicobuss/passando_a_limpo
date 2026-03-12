<?php

/** @var yii\web\View $this */
/** @var array $stats */

use yii\bootstrap5\Html;

$this->title = 'Painel do Candidato';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="candidate-panel-index candidate-dashboard">
    <section class="candidate-dashboard__hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-7">
                <span class="app-section-eyebrow">Área do candidato</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Um painel mais analítico para acompanhar sua produção, responder ao debate e voltar rapidamente para as propostas prioritárias.</p>
                <div class="candidate-dashboard__actions mt-4 d-flex flex-wrap gap-2">
                    <?= Html::a('Gerenciar propostas', ['/proposal/index'], ['class' => 'btn btn-primary app-btn']) ?>
                    <?= Html::a('Nova proposta', ['/proposal/create'], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                    <?= Html::a('Minha conta', ['/site/my-account'], ['class' => 'btn btn-outline-primary app-btn app-btn--ghost']) ?>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="candidate-dashboard__metric-grid">
                    <article class="candidate-dashboard__metric-card">
                        <span>Total de propostas</span>
                        <strong><?= (int) $stats['totalProposals'] ?></strong>
                    </article>
                    <article class="candidate-dashboard__metric-card">
                        <span>Média de votos</span>
                        <strong><?= number_format((float) $stats['avgScore'], 2, ',', '.') ?></strong>
                    </article>
                    <article class="candidate-dashboard__metric-card candidate-dashboard__metric-card--accent">
                        <span>Sugestões pendentes</span>
                        <strong><?= (int) $stats['pendingSuggestions'] ?></strong>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-6">
            <section class="candidate-dashboard__panel h-100">
                <div class="candidate-dashboard__panel-header">
                    <div>
                        <span class="app-section-eyebrow">Debate recente</span>
                        <h2 class="h4 mt-3 mb-0">Comentários recentes</h2>
                    </div>
                    <span class="home-score-chip"><?= count($stats['recentComments']) ?></span>
                </div>

                <?php if (!empty($stats['recentComments'])): ?>
                    <div class="candidate-dashboard__feed mt-4">
                        <?php foreach ($stats['recentComments'] as $comment): ?>
                            <article class="candidate-dashboard__feed-item">
                                <span class="candidate-dashboard__feed-label">Comentário em proposta</span>
                                <h3 class="candidate-dashboard__feed-title">
                                    <?= Html::a(
                                        Html::encode(mb_strimwidth((string) $comment->content, 0, 90, '...')),
                                        ['/proposal/view', 'id' => $comment->proposal_id]
                                    ) ?>
                                </h3>
                                <p class="mb-0">Publicado em <?= date('d/m/Y H:i', (int) $comment->created_at) ?>.</p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="app-empty-state mt-4">
                        <h3 class="h6 mb-2">Sem comentários recentes</h3>
                        <p class="mb-0">As interações dos cidadãos nas suas propostas aparecerão aqui para resposta rápida.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="col-xl-6">
            <section class="candidate-dashboard__panel h-100">
                <div class="candidate-dashboard__panel-header">
                    <div>
                        <span class="app-section-eyebrow">Base de atuação</span>
                        <h2 class="h4 mt-3 mb-0">Minhas propostas</h2>
                    </div>
                    <span class="home-score-chip"><?= count($stats['myProposals']) ?></span>
                </div>

                <?php if (!empty($stats['myProposals'])): ?>
                    <div class="candidate-dashboard__proposal-list mt-4">
                        <?php foreach ($stats['myProposals'] as $proposal): ?>
                            <article class="candidate-dashboard__proposal-card">
                                <div>
                                    <span class="candidate-dashboard__feed-label">Proposta monitorada</span>
                                    <h3 class="candidate-dashboard__feed-title mb-1">
                                        <?= Html::a(Html::encode((string) $proposal->title), ['/proposal/view', 'id' => $proposal->id]) ?>
                                    </h3>
                                    <p class="mb-0">Score público atual: <?= (int) $proposal->score ?></p>
                                </div>
                                <span class="home-score-chip"><?= (int) $proposal->score ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="app-empty-state mt-4">
                        <h3 class="h6 mb-2">Sem propostas cadastradas</h3>
                        <p class="mb-3">Publique sua primeira proposta para ativar o acompanhamento do painel.</p>
                        <?= Html::a('Criar proposta', ['/proposal/create'], ['class' => 'btn btn-primary app-btn']) ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
