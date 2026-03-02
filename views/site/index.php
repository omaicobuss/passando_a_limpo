<?php

/** @var yii\web\View $this */
/** @var app\models\Proposal[] $latestProposals */
/** @var app\models\Election[] $activeElections */

use yii\bootstrap5\Html;

$this->title = 'Passando a Limpo';

$totalScore = 0;
foreach ($latestProposals as $proposal) {
    $totalScore += (int) $proposal->score;
}
?>
<div class="site-index home-modern">
    <section class="home-hero mb-5">
        <div class="home-hero__surface">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="home-kicker">GOVERNANCA ABERTA</span>
                    <h1 class="home-title mt-3 mb-3">Politica com prestacao de contas em tempo real</h1>
                    <p class="home-subtitle mb-4">
                        O Passando a Limpo conecta eleitores e candidatos com propostas, votacao publica, comentarios e acompanhamento de execucao.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?= Html::a('Explorar propostas', ['/proposal/index'], ['class' => 'btn btn-primary btn-lg home-btn']) ?>
                        <?= Html::a('Conhecer candidatos', ['/candidate/index'], ['class' => 'btn btn-outline-light btn-lg home-btn']) ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="home-metrics">
                        <div class="home-metrics__item">
                            <div class="home-metrics__value"><?= count($activeElections) ?></div>
                            <div class="home-metrics__label">Eleicoes ativas</div>
                        </div>
                        <div class="home-metrics__item">
                            <div class="home-metrics__value"><?= count($latestProposals) ?></div>
                            <div class="home-metrics__label">Propostas recentes</div>
                        </div>
                        <div class="home-metrics__item">
                            <div class="home-metrics__value"><?= $totalScore ?></div>
                            <div class="home-metrics__label">Saldo de votos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="home-section-title m-0">Eleicoes em destaque</h2>
            <?= Html::a('Ver todas', ['/election/index'], ['class' => 'home-inline-link']) ?>
        </div>
        <div class="row g-3">
            <?php foreach ($activeElections as $election): ?>
                <div class="col-md-6 col-lg-3">
                    <article class="card h-100 home-card home-card--election">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 card-title fw-semibold"><?= Html::encode($election->title) ?></h3>
                            <p class="small text-secondary mb-3">
                                <?= Html::encode((string) $election->start_date) ?> a <?= Html::encode((string) $election->end_date) ?>
                            </p>
                            <div class="mt-auto">
                                <?= Html::a('Ver eleicao', ['/election/view', 'id' => $election->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="home-section-title m-0">Propostas em alta</h2>
            <?= Html::a('Filtrar propostas', ['/proposal/index'], ['class' => 'home-inline-link']) ?>
        </div>
        <div class="row g-3">
            <?php foreach ($latestProposals as $proposal): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="card h-100 home-card home-card--proposal">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h3 class="h6 card-title fw-semibold mb-0"><?= Html::encode($proposal->title) ?></h3>
                                <span class="badge text-bg-light border">Score <?= (int) $proposal->score ?></span>
                            </div>
                            <p class="small text-secondary mb-2"><?= Html::encode((string) $proposal->theme ?: 'Tema geral') ?></p>
                            <p class="mb-3"><?= Html::encode(mb_strimwidth(strip_tags($proposal->content), 0, 130, '...')) ?></p>
                            <div class="mt-auto">
                                <?= Html::a('Abrir proposta', ['/proposal/view', 'id' => $proposal->id], ['class' => 'btn btn-sm btn-primary']) ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (empty($activeElections) && empty($latestProposals)): ?>
        <section class="home-empty mt-4">
            <h3 class="h5 mb-2">Comece a construir o debate publico</h3>
            <p class="text-secondary mb-3">Cadastre eleicoes, candidatos e propostas para ativar a plataforma.</p>
            <?= Html::a('Criar primeira eleicao', ['/election/create'], ['class' => 'btn btn-primary']) ?>
        </section>
    <?php endif; ?>
</div>
