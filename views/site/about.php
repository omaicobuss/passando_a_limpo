<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about app-collection-page">
    <section class="app-page-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <span class="app-section-eyebrow">Sobre a plataforma</span>
                <h1 class="app-page-title mt-3 mb-2"><?= Html::encode($this->title) ?></h1>
                <p class="app-page-subtitle mb-0">Conheça o propósito e a estrutura do sistema Passando a Limpo.</p>
            </div>
        </div>
    </section>

    <div class="card app-filter-card mt-4">
        <div class="card-body p-4 p-md-5 proposal-detail-body">
            <p>
                This is the About page. You may modify the following file to customize its content:
            </p>
            <code><?= __FILE__ ?></code>
        </div>
    </div>
</div>
