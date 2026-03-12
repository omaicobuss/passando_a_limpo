<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\CandidateUpgradeRequest;
use app\models\ProposalCommentReport;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$currentRoute = Yii::$app->controller->route;
$bodyClass = 'app-body route-' . str_replace(['/', '_'], '-', $currentRoute);
$isHome = $currentRoute === 'site/index';

$items = [
    ['label' => 'Início', 'url' => ['/site/index'], 'activeRoutes' => ['site/index']],
    ['label' => 'Eleições', 'url' => ['/election/index'], 'activeRoutes' => ['election/index', 'election/view', 'election/create', 'election/update']],
    ['label' => 'Candidatos', 'url' => ['/candidate/index'], 'activeRoutes' => ['candidate/index', 'candidate/view', 'candidate/create', 'candidate/update']],
    ['label' => 'Propostas', 'url' => ['/proposal/index'], 'activeRoutes' => ['proposal/index', 'proposal/view', 'proposal/create', 'proposal/update']],
];

$pendingRequestsCount = 0;
$reportedCommentsCount = 0;

if (!Yii::$app->user->isGuest) {
    $items[] = ['label' => 'Minha Conta', 'url' => ['/site/my-account'], 'activeRoutes' => ['site/my-account', 'site/my-candidates', 'site/my-proposals', 'site/my-comments', 'site/my-suggestions', 'site/my-proposal-votes', 'site/my-suggestion-votes', 'site/my-status-updates']];

    if (Yii::$app->user->can('admin')) {
        $pendingRequestsCount = (int) CandidateUpgradeRequest::find()
            ->where(['status' => CandidateUpgradeRequest::STATUS_PENDING])
            ->count();
        $requestsLabel = $pendingRequestsCount > 0
            ? sprintf('Solicitações (%d)', $pendingRequestsCount)
            : 'Solicitações';

        $reportedCommentsCount = (int) ProposalCommentReport::find()
            ->select('comment_id')
            ->distinct()
            ->count('comment_id');
        $reportedCommentsLabel = $reportedCommentsCount > 0
            ? sprintf('Moderar comentários (%d)', $reportedCommentsCount)
            : 'Moderar comentários';

        $items[] = ['label' => $requestsLabel, 'url' => ['/site/candidate-requests'], 'activeRoutes' => ['site/candidate-requests']];
        $items[] = ['label' => $reportedCommentsLabel, 'url' => ['/proposal-comment/reported'], 'activeRoutes' => ['proposal-comment/reported']];
    }

    if (Yii::$app->user->can('candidate')) {
        $items[] = ['label' => 'Painel do candidato', 'url' => ['/candidate-panel/index'], 'activeRoutes' => ['candidate-panel/index']];
    }
}

$renderNavItem = static function (array $item) use ($currentRoute): string {
    $isActive = false;
    foreach ($item['activeRoutes'] ?? [] as $route) {
        if ($currentRoute === $route || str_starts_with($currentRoute, $route . '/')) {
            $isActive = true;
            break;
        }
    }

    $linkOptions = ['class' => 'nav-link app-nav__link' . ($isActive ? ' active' : '')];
    return Html::tag('li', Html::a($item['label'], $item['url'], $linkOptions), ['class' => 'nav-item']);
};

$toplineText = 'Transparência pública, participação cidadã e acompanhamento pós-eleição em um só fluxo.';
if (!Yii::$app->user->isGuest && Yii::$app->user->can('admin')) {
    $toplineText = sprintf('Administração ativa: %d solicitações pendentes e %d comentários em moderação.', $pendingRequestsCount, $reportedCommentsCount);
} elseif (!Yii::$app->user->isGuest && Yii::$app->user->can('candidate')) {
    $toplineText = 'Área de candidato liberada para publicar propostas, responder sugestões e registrar avanços.';
} elseif (!Yii::$app->user->isGuest) {
    $toplineText = 'Sua conta está pronta para votar, comentar, sugerir melhorias e acompanhar resultados.';
}

$footerLinks = [
    ['label' => 'Página inicial', 'url' => ['/site/index']],
    ['label' => 'Eleições', 'url' => ['/election/index']],
    ['label' => 'Candidatos', 'url' => ['/candidate/index']],
    ['label' => 'Propostas', 'url' => ['/proposal/index']],
    ['label' => 'Minha conta', 'url' => ['/site/my-account']],
    ['label' => 'Contato', 'url' => ['/site/contact']],
];

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100 <?= Html::encode($bodyClass) ?>">
<?php $this->beginBody() ?>

<header id="header">
    <div class="app-topline">
        <div class="container-xl d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
            <span class="app-topline__copy"><?= Html::encode($toplineText) ?></span>
            <div class="app-topline__meta">
                <?php if (Yii::$app->user->isGuest): ?>
                    <span>Crie uma conta para participar dos debates e registrar votos.</span>
                <?php else: ?>
                    <span>Conectado como <?= Html::encode((string) Yii::$app->user->identity->username) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="app-navbar-wrap">
        <div class="container-xl">
            <nav class="navbar navbar-expand-xl app-navbar">
                <a class="navbar-brand app-brand" href="<?= Html::encode(Url::to(Yii::$app->homeUrl)) ?>">
                    <span class="app-brand__mark">PL</span>
                    <span class="app-brand__text">
                        <strong>Passando a Limpo</strong>
                        <small>Prestação de contas e debate público</small>
                    </span>
                </a>

                <button class="navbar-toggler app-navbar__toggler" type="button" data-bs-toggle="collapse" data-bs-target="#app-main-nav" aria-controls="app-main-nav" aria-expanded="false" aria-label="Alternar navegação">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="app-main-nav">
                    <ul class="navbar-nav app-nav ms-auto me-xl-3 mb-3 mb-xl-0">
                        <?php foreach ($items as $item): ?>
                            <?= $renderNavItem($item) ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="app-nav__cta d-flex flex-column flex-xl-row gap-2">
                        <?php if (Yii::$app->user->isGuest): ?>
                            <?= Html::a('Cadastro', ['/site/signup'], ['class' => 'btn btn-outline-primary app-btn app-btn--ghost']) ?>
                            <?= Html::a('Login', ['/site/login'], ['class' => 'btn btn-primary app-btn']) ?>
                        <?php else: ?>
                            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'm-0']) ?>
                            <?= Html::submitButton('Sair (' . Html::encode((string) Yii::$app->user->identity->username) . ')', ['class' => 'btn btn-outline-primary app-btn app-btn--ghost w-100']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</header>

<main id="main" class="app-main flex-shrink-0<?= $isHome ? ' app-main--home' : '' ?>" role="main">
    <div class="app-main__ornament app-main__ornament--one"></div>
    <div class="app-main__ornament app-main__ornament--two"></div>
    <div class="container-xl app-main__container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <div class="app-breadcrumbs"><?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?></div>
        <?php endif ?>
        <div class="app-alert-stack"><?= Alert::widget() ?></div>
        <div class="app-content<?= $isHome ? ' app-content--home' : '' ?>">
            <?= $content ?>
        </div>
    </div>
</main>

<footer id="footer" class="app-footer mt-auto">
    <div class="container-xl">
        <div class="app-footer__surface">
            <div class="row g-4">
                <div class="col-lg-5">
                    <span class="app-section-eyebrow">Participação cívica</span>
                    <h2 class="app-footer__title">Uma interface pensada para acompanhar eleições, candidaturas e compromissos públicos.</h2>
                    <p class="app-footer__text mb-0">O sistema foi redesenhado com linguagem visual inspirada em produtos financeiros editoriais: contraste alto, cartões amplos, navegação clara e foco em comparação de informações.</p>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <h3 class="app-footer__heading">Mapa rápido</h3>
                    <ul class="app-footer__links list-unstyled mb-0">
                        <?php foreach (array_slice($footerLinks, 0, 4) as $link): ?>
                            <li><?= Html::a($link['label'], $link['url']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <h3 class="app-footer__heading">Conta</h3>
                    <ul class="app-footer__links list-unstyled mb-0">
                        <?php foreach (array_slice($footerLinks, 4) as $link): ?>
                            <li><?= Html::a($link['label'], $link['url']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h3 class="app-footer__heading">Estado</h3>
                    <div class="app-footer__status">
                        <span class="app-footer__status-dot"></span>
                        Plataforma online
                    </div>
                    <p class="app-footer__text app-footer__text--small mb-0">Copyright © <?= date('Y') ?> Passando a Limpo.</p>
                </div>
            </div>
            <div class="app-footer__bottom d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
                <span>Design editorial com foco em leitura, comparação e confiabilidade.</span>
                <span>Baseado em Yii2 e Bootstrap 5.</span>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
