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

$serviceCards = [
    [
        'eyebrow' => '01',
        'title' => 'Radar de eleições',
        'text' => 'Acompanhe calendários, mandatos em disputa e status de cada processo eleitoral em uma leitura rápida.',
    ],
    [
        'eyebrow' => '02',
        'title' => 'Comparação de propostas',
        'text' => 'Cruze temas, candidatos e evolução do cumprimento para entender convergências e diferenças.',
    ],
    [
        'eyebrow' => '03',
        'title' => 'Debate público auditável',
        'text' => 'Comentários, denúncias e sugestões ficam organizados em fluxos rastreáveis para moderação responsável.',
    ],
    [
        'eyebrow' => '04',
        'title' => 'Pós-eleição contínuo',
        'text' => 'Após o pleito, candidatos registram andamento das promessas e a sociedade acompanha a execução.',
    ],
];

$processSteps = [
    ['number' => '01', 'title' => 'Explore o cenário', 'text' => 'Descubra eleições abertas, candidatos e temas em destaque.'],
    ['number' => '02', 'title' => 'Compare propostas', 'text' => 'Avalie score, contexto e histórico de publicação de cada compromisso.'],
    ['number' => '03', 'title' => 'Participe do debate', 'text' => 'Vote, comente e sugira melhorias diretamente na proposta.'],
    ['number' => '04', 'title' => 'Acompanhe resultados', 'text' => 'Consulte atualizações de status e evolução após a eleição.'],
];
?>
<div class="site-index home-modern">
    <section class="home-hero mb-5">
        <div class="home-hero__surface">
            <div class="row align-items-center g-4 g-xxl-5">
                <div class="col-lg-7 col-xxl-6">
                    <span class="app-section-eyebrow">Plataforma cívica</span>
                    <h1 class="home-title mt-3 mb-3">Compare propostas, acompanhe eleições e cobre resultados com clareza editorial.</h1>
                    <p class="home-subtitle mb-4">
                        O Passando a Limpo traduz a lógica visual de plataformas de comparação para o contexto democrático: leitura rápida, evidência pública e acompanhamento contínuo das promessas.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <?= Html::a('Explorar propostas', ['/proposal/index'], ['class' => 'btn btn-primary btn-lg app-btn']) ?>
                        <?= Html::a('Ver eleições ativas', ['/election/index'], ['class' => 'btn btn-outline-light btn-lg app-btn app-btn--light']) ?>
                    </div>
                    <div class="home-bullet-row mt-4">
                        <span>Votação pública</span>
                        <span>Moderação auditável</span>
                        <span>Acompanhamento pós-eleição</span>
                    </div>
                </div>
                <div class="col-lg-5 col-xxl-6">
                    <div class="home-showcase">
                        <div class="home-showcase__panel home-showcase__panel--primary">
                            <span class="home-showcase__eyebrow">Panorama atual</span>
                            <div class="home-showcase__metric-grid">
                                <div class="home-showcase__metric">
                                    <strong><?= count($activeElections) ?></strong>
                                    <span>Eleições ativas</span>
                                </div>
                                <div class="home-showcase__metric">
                                    <strong><?= count($latestProposals) ?></strong>
                                    <span>Propostas recentes</span>
                                </div>
                                <div class="home-showcase__metric">
                                    <strong><?= $totalScore ?></strong>
                                    <span>Saldo público de votos</span>
                                </div>
                                <div class="home-showcase__metric">
                                    <strong>24/7</strong>
                                    <span>Consulta contínua</span>
                                </div>
                            </div>
                        </div>
                        <div class="home-showcase__panel home-showcase__panel--secondary">
                            <p class="home-showcase__card-title mb-2">Leitura orientada por decisão</p>
                            <p class="mb-0">Assim como no site de referência, o foco está em comparar informação crítica sem poluir a navegação.</p>
                        </div>
                        <div class="home-showcase__floating">
                            <span class="home-showcase__floating-label">Fluxo principal</span>
                            <strong>Explorar → Comparar → Participar → Acompanhar</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section mb-5">
        <div class="home-section__heading d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <span class="app-section-eyebrow">Serviços da plataforma</span>
                <h2 class="home-section-title mt-2 mb-0">O núcleo do sistema foi reorganizado como uma vitrine de comparação e confiança.</h2>
            </div>
            <?= Html::a('Conhecer candidatos', ['/candidate/index'], ['class' => 'home-inline-link']) ?>
        </div>
        <div class="row g-3 g-xl-4">
            <?php foreach ($serviceCards as $card): ?>
                <div class="col-md-6 col-xl-3">
                    <article class="home-service-card h-100">
                        <span class="home-service-card__number"><?= Html::encode($card['eyebrow']) ?></span>
                        <h3 class="home-service-card__title"><?= Html::encode($card['title']) ?></h3>
                        <p class="home-service-card__text mb-0"><?= Html::encode($card['text']) ?></p>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="home-section mb-5">
        <div class="home-section__heading d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
            <div>
                <span class="app-section-eyebrow">Coleções em destaque</span>
                <h2 class="home-section-title mt-2 mb-0">Eleições e propostas aparecem em cartões largos, com leitura rápida e ação direta.</h2>
            </div>
            <?= Html::a('Abrir catálogo completo', ['/proposal/index'], ['class' => 'home-inline-link']) ?>
        </div>

        <div class="row g-4">
            <div class="col-xl-5">
                <div class="home-feature-stack">
                    <div class="home-feature-stack__header">
                        <h3 class="h5 mb-0">Eleições em destaque</h3>
                        <?= Html::a('Ver todas', ['/election/index'], ['class' => 'home-inline-link']) ?>
                    </div>
                    <?php if (!empty($activeElections)): ?>
                        <?php foreach ($activeElections as $election): ?>
                            <article class="home-feature-card home-feature-card--election">
                                <div>
                                    <span class="home-feature-card__label">Eleição ativa</span>
                                    <h4 class="home-feature-card__title"><?= Html::encode($election->title) ?></h4>
                                    <p class="home-feature-card__text mb-0"><?= Html::encode((string) $election->start_date) ?> até <?= Html::encode((string) $election->end_date) ?></p>
                                </div>
                                <?= Html::a('Ver eleição', ['/election/view', 'id' => $election->id], ['class' => 'btn btn-outline-primary app-btn app-btn--ghost']) ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="home-empty">
                            <h3 class="h6 mb-2">Nenhuma eleição ativa</h3>
                            <p class="mb-0">Assim que novos ciclos forem cadastrados, eles aparecerão aqui com destaque editorial.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="row g-3">
                    <?php if (!empty($latestProposals)): ?>
                        <?php foreach ($latestProposals as $proposal): ?>
                            <div class="col-md-6">
                                <article class="home-proposal-card h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <span class="home-feature-card__label">Proposta monitorada</span>
                                            <h3 class="home-proposal-card__title mb-1"><?= Html::encode($proposal->title) ?></h3>
                                            <p class="home-proposal-card__meta mb-0"><?= Html::encode((string) ($proposal->theme ?: 'Tema geral')) ?> · <?= Html::encode((string) ($proposal->candidate->display_name ?? 'Sem candidato')) ?></p>
                                        </div>
                                        <span class="home-score-chip">Score <?= (int) $proposal->score ?></span>
                                    </div>
                                    <p class="home-proposal-card__text"><?= Html::encode(mb_strimwidth(strip_tags($proposal->content), 0, 180, '...')) ?></p>
                                    <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                                        <span class="home-status-line"><?= Html::encode(\app\models\Proposal::statusOptions()[$proposal->fulfillment_status] ?? $proposal->fulfillment_status) ?></span>
                                        <?= Html::a('Abrir proposta', ['/proposal/view', 'id' => $proposal->id], ['class' => 'btn btn-primary app-btn']) ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="home-empty h-100">
                                <h3 class="h6 mb-2">Nenhuma proposta recente</h3>
                                <p class="mb-3">As propostas publicadas aparecerão aqui com visual de comparação e acesso rápido.</p>
                                <?= Html::a('Explorar catálogo', ['/proposal/index'], ['class' => 'btn btn-primary app-btn']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section mb-5">
        <div class="home-process">
            <div class="row g-4 align-items-center">
                <div class="col-lg-4">
                    <span class="app-section-eyebrow">Fluxo de participação</span>
                    <h2 class="home-section-title mt-2">Um processo simples para transformar dados eleitorais em decisão pública informada.</h2>
                    <p class="home-process__intro mb-0">A referência visual organiza a jornada em blocos sequenciais. Aqui, a mesma lógica orienta descoberta, comparação e acompanhamento.</p>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <?php foreach ($processSteps as $step): ?>
                            <div class="col-md-6">
                                <article class="home-process-card h-100">
                                    <span class="home-process-card__number"><?= Html::encode($step['number']) ?></span>
                                    <h3 class="home-process-card__title"><?= Html::encode($step['title']) ?></h3>
                                    <p class="home-process-card__text mb-0"><?= Html::encode($step['text']) ?></p>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-cta-band">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Participe da leitura pública</span>
                <h2 class="home-section-title mt-2 mb-2">Entre para votar, comentar e acompanhar o cumprimento das propostas depois da eleição.</h2>
                <p class="mb-0">O novo layout prioriza confiança, contraste e contexto. Agora é só usar a plataforma com a mesma fluidez de uma vitrine editorial moderna.</p>
            </div>
            <div class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column align-items-lg-end gap-3 justify-content-lg-center">
                <?= Html::a('Criar conta', ['/site/signup'], ['class' => 'btn btn-light btn-lg app-btn app-btn--solid-light']) ?>
                <?= Html::a('Abrir propostas', ['/proposal/index'], ['class' => 'btn btn-outline-light btn-lg app-btn app-btn--light']) ?>
            </div>
        </div>
    </section>
</div>
