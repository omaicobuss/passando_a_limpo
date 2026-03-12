<?php

/** @var yii\web\View $this */
/** @var app\models\Proposal $model */
/** @var app\models\ProposalComment $commentModel */
/** @var app\models\ProposalSuggestion $suggestionModel */
/** @var app\models\ProposalStatusUpdate $statusModel */
/** @var app\models\ProposalComment[] $rootComments */
/** @var app\models\ProposalRevision|null $latestRevision */
/** @var app\models\ProposalRevision[] $previousRevisions */

use app\models\Proposal;
use app\models\ProposalSuggestion;
use app\models\ProposalSuggestionVote;
use app\models\ProposalVote;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$latestRevision = $model->latestRevision;
$previousRevisions = array_slice($model->revisions, 1);
$isEditLockedByDeadline = $model->isEditLockedByElectionDeadline();
$currentProposalVoteValue = 0;
$currentSuggestionVoteMap = [];
$proposalStatusLabel = Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status;
$proposalSummary = StringHelper::truncateWords(strip_tags((string) $model->content), 34, '...');
$totalSuggestions = count($model->suggestions);
$totalStatusUpdates = count($model->statusUpdates);
$totalComments = count($model->comments);
$voteUrl = Url::to(['/proposal/vote']);
$suggestionVoteUrl = Url::to(['/proposal-suggestion/vote']);
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;

$this->registerCss(<<<CSS
.vote-selected {
        box-shadow: 0 0 0 0.2rem rgba(252, 182, 80, 0.28);
}
CSS);

$this->registerJs(<<<JS
const applyVoteVisualState = (selector, itemId, selectedValue) => {
        const selectedValueAsString = String(selectedValue);
        document.querySelectorAll(`\${selector}[data-id="\${itemId}"]`).forEach((button) => {
                const isPositiveButton = button.dataset.value === '1';
                const isSelectedButton = button.dataset.value === selectedValueAsString;

                button.classList.remove('btn-success', 'btn-danger', 'btn-outline-success', 'btn-outline-danger', 'active', 'vote-selected');

                if (isPositiveButton) {
                        button.classList.add(isSelectedButton ? 'btn-success' : 'btn-outline-success');
                } else {
                        button.classList.add(isSelectedButton ? 'btn-danger' : 'btn-outline-danger');
                }

                button.classList.toggle('active', isSelectedButton);
                button.classList.toggle('vote-selected', isSelectedButton);
                button.setAttribute('aria-pressed', isSelectedButton ? 'true' : 'false');
        });
};

document.querySelectorAll('.btn-vote').forEach((button) => {
    button.addEventListener('click', function () {
        fetch('{$voteUrl}', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                proposal_id: this.dataset.id,
                value: this.dataset.value,
                {$csrfParam}: '{$csrf}'
            })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('proposal-score').textContent = data.score;
                applyVoteVisualState('.btn-vote', this.dataset.id, this.dataset.value);
            } else {
                alert(data.message || 'Falha ao votar');
            }
        });
    });
});

document.querySelectorAll('.btn-suggestion-vote').forEach((button) => {
    button.addEventListener('click', function () {
        const suggestionId = this.dataset.id;
        fetch('{$suggestionVoteUrl}', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                suggestion_id: suggestionId,
                value: this.dataset.value,
                {$csrfParam}: '{$csrf}'
            })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('suggestion-score-' + suggestionId).textContent = data.score;
                applyVoteVisualState('.btn-suggestion-vote', suggestionId, this.dataset.value);
            } else {
                alert(data.message || 'Falha ao votar');
            }
        });
    });
});
JS);

if (!Yii::$app->user->isGuest) {
    $currentUserId = (int) Yii::$app->user->id;

    $currentProposalVoteValue = (int) (ProposalVote::find()
        ->select(ProposalVote::valueColumn())
        ->where(['proposal_id' => (int) $model->id, 'user_id' => $currentUserId])
        ->scalar() ?: 0);

    $suggestionIds = array_map(
        static fn (ProposalSuggestion $suggestion): int => (int) $suggestion->id,
        $model->suggestions
    );

    if (!empty($suggestionIds)) {
        foreach (ProposalSuggestionVote::find()
            ->select(['suggestion_id', 'value'])
            ->where(['user_id' => $currentUserId, 'suggestion_id' => $suggestionIds])
            ->asArray()
            ->all() as $voteRow) {
            $currentSuggestionVoteMap[(int) $voteRow['suggestion_id']] = (int) $voteRow['value'];
        }
    }
}
?>
<div class="proposal-view proposal-detail-page">
    <section class="proposal-detail-hero mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <span class="app-section-eyebrow">Proposta monitorada</span>
                <h1 class="proposal-detail-hero__title mt-3 mb-3"><?= Html::encode($model->title) ?></h1>
                <p class="proposal-detail-hero__summary mb-4"><?= Html::encode($proposalSummary) ?></p>
                <div class="proposal-detail-hero__meta">
                    <span><strong>Candidato</strong> <?= Html::encode((string) ($model->candidate->display_name ?? '-')) ?></span>
                    <span><strong>Eleição</strong> <?= Html::encode((string) ($model->election->title ?? '-')) ?></span>
                    <span><strong>Tema</strong> <?= Html::encode((string) ($model->theme ?: 'Tema geral')) ?></span>
                    <span><strong>Status</strong> <?= Html::encode((string) $proposalStatusLabel) ?></span>
                </div>
                <div class="proposal-detail-hero__actions mt-4 d-flex flex-wrap gap-2">
                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('updateOwnProposal', ['proposal' => $model]) && !$isEditLockedByDeadline): ?>
                        <?= Html::a('Editar proposta', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary app-btn app-btn--ghost']) ?>
                    <?php endif; ?>
                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('admin') && !empty($previousRevisions)): ?>
                        <?= Html::a('Histórico de versões', '#proposal-revision-history', ['class' => 'btn btn-outline-primary app-btn app-btn--ghost']) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="proposal-score-panel">
                    <span class="proposal-score-panel__label">Score público</span>
                    <strong id="proposal-score"><?= (int) $model->score ?></strong>
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <div class="proposal-score-panel__votes">
                            <button class="btn <?= $currentProposalVoteValue === 1 ? 'btn-success active vote-selected' : 'btn-outline-success' ?> btn-vote" data-value="1" data-id="<?= (int) $model->id ?>" aria-pressed="<?= $currentProposalVoteValue === 1 ? 'true' : 'false' ?>">+1</button>
                            <button class="btn <?= $currentProposalVoteValue === -1 ? 'btn-danger active vote-selected' : 'btn-outline-danger' ?> btn-vote" data-value="-1" data-id="<?= (int) $model->id ?>" aria-pressed="<?= $currentProposalVoteValue === -1 ? 'true' : 'false' ?>">-1</button>
                        </div>
                    <?php endif; ?>
                    <div class="proposal-score-panel__stats">
                        <div>
                            <span>Sugestões</span>
                            <strong><?= $totalSuggestions ?></strong>
                        </div>
                        <div>
                            <span>Comentários</span>
                            <strong><?= $totalComments ?></strong>
                        </div>
                        <div>
                            <span>Atualizações</span>
                            <strong><?= $totalStatusUpdates ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($isEditLockedByDeadline): ?>
        <div class="alert alert-warning mb-3">
            Prazo da eleição encerrado: esta proposta não aceita mais edições.
        </div>
    <?php endif; ?>

    <?php if ($model->hasRevisionHistory() && $latestRevision !== null): ?>
        <div class="proposal-highlight-note mb-4">
            <div>
                <strong>Última edição registrada:</strong>
                <?= Html::encode(Yii::$app->formatter->asDatetime((int) $latestRevision->created_at, 'php:d/m/Y H:i')) ?>
                por <?= Html::encode($latestRevision->editor->username ?? 'sistema') ?>.
            </div>
            <span class="home-score-chip">Versão atual: <?= (int) $model->getCurrentVersionNumber() ?></span>
        </div>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <section class="proposal-section proposal-section--content mb-4">
                <div class="proposal-section__header">
                    <div>
                        <span class="app-section-eyebrow">Conteúdo completo</span>
                        <h2 class="h4 mt-3 mb-0">Compromisso detalhado</h2>
                    </div>
                </div>
                <div class="proposal-detail-body mt-4"><?= nl2br(Html::encode($model->content)) ?></div>
            </section>

            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('admin') && !empty($previousRevisions)): ?>
                <section class="proposal-section mb-4" id="proposal-revision-history">
                    <div class="proposal-section__header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div>
                            <span class="app-section-eyebrow">Auditoria</span>
                            <h2 class="h4 mt-3 mb-0">Histórico de versões</h2>
                        </div>
                        <span class="home-score-chip"><?= count($previousRevisions) ?> anteriores</span>
                    </div>
                    <div class="accordion accordion-flush proposal-revision-accordion mt-4" id="proposal-revisions-accordion">
                        <?php foreach ($previousRevisions as $index => $revision): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="proposal-revision-heading-<?= (int) $revision->id ?>">
                                    <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#proposal-revision-collapse-<?= (int) $revision->id ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="proposal-revision-collapse-<?= (int) $revision->id ?>">
                                        Versão <?= (int) $revision->version_number ?>
                                        · <?= Html::encode(Yii::$app->formatter->asDatetime((int) $revision->created_at, 'php:d/m/Y H:i')) ?>
                                        · <?= Html::encode($revision->editor->username ?? 'sistema') ?>
                                    </button>
                                </h2>
                                <div id="proposal-revision-collapse-<?= (int) $revision->id ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="proposal-revision-heading-<?= (int) $revision->id ?>" data-bs-parent="#proposal-revisions-accordion">
                                    <div class="accordion-body">
                                        <div class="app-record-meta mb-3">
                                            <span><strong>Título</strong> <?= Html::encode((string) $revision->title) ?></span>
                                            <span><strong>Tema</strong> <?= Html::encode((string) $revision->theme) ?></span>
                                            <span><strong>Status</strong> <?= Html::encode((string) (Proposal::statusOptions()[$revision->fulfillment_status] ?? $revision->fulfillment_status)) ?></span>
                                        </div>
                                        <div class="proposal-detail-body proposal-detail-body--revision"><?= nl2br(Html::encode($revision->content)) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="proposal-section mb-4">
                <div class="proposal-section__header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <span class="app-section-eyebrow">Participação colaborativa</span>
                        <h2 class="h4 mt-3 mb-0">Sugestões</h2>
                    </div>
                    <span class="home-score-chip"><?= $totalSuggestions ?></span>
                </div>

                <?php if (!Yii::$app->user->isGuest): ?>
                    <form id="proposal-suggestion-form" method="post" action="<?= Url::to(['/proposal-suggestion/create']) ?>" class="proposal-inline-form mt-4">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="proposal_id" value="<?= (int) $model->id ?>">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-4"><input class="form-control" name="title" placeholder="Título da sugestão"></div>
                            <div class="col-lg-6"><input class="form-control" name="content" placeholder="Descreva sua sugestão"></div>
                            <div class="col-lg-2 d-grid"><button class="btn btn-outline-primary app-btn app-btn--ghost">Enviar</button></div>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if (!empty($model->suggestions)): ?>
                    <div class="proposal-suggestion-list mt-4">
                        <?php foreach ($model->suggestions as $suggestion): ?>
                            <?php $suggestionVoteValue = (int) ($currentSuggestionVoteMap[(int) $suggestion->id] ?? 0); ?>
                            <article class="proposal-suggestion-card">
                                <div class="proposal-suggestion-card__header">
                                    <div>
                                        <h3 class="proposal-suggestion-card__title mb-1">
                                            <?= Html::a(Html::encode($suggestion->title), ['/proposal-suggestion/view', 'id' => $suggestion->id]) ?>
                                        </h3>
                                        <p class="mb-0"><?= Html::encode($suggestion->content) ?></p>
                                    </div>
                                    <span class="app-record-chip app-record-chip--soft"><?= Html::encode((string) (ProposalSuggestion::statusOptions()[$suggestion->status] ?? $suggestion->status)) ?></span>
                                </div>
                                <div class="proposal-suggestion-card__footer">
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <?php if (!Yii::$app->user->isGuest): ?>
                                            <button class="btn btn-sm <?= $suggestionVoteValue === 1 ? 'btn-success active vote-selected' : 'btn-outline-success' ?> btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="1" aria-pressed="<?= $suggestionVoteValue === 1 ? 'true' : 'false' ?>">+1</button>
                                            <button class="btn btn-sm <?= $suggestionVoteValue === -1 ? 'btn-danger active vote-selected' : 'btn-outline-danger' ?> btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="-1" aria-pressed="<?= $suggestionVoteValue === -1 ? 'true' : 'false' ?>">-1</button>
                                        <?php endif; ?>
                                        <span>Score: <strong id="suggestion-score-<?= (int) $suggestion->id ?>"><?= $suggestion->getScore() ?></strong></span>
                                    </div>
                                    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('moderateSuggestion', ['suggestion' => $suggestion])): ?>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <?= Html::a('Aprovar', ['/proposal-suggestion/moderate', 'id' => $suggestion->id, 'status' => ProposalSuggestion::STATUS_APPROVED], ['class' => 'btn btn-sm btn-outline-primary app-btn app-btn--ghost', 'data-method' => 'post']) ?>
                                            <?= Html::a('Rejeitar', ['/proposal-suggestion/moderate', 'id' => $suggestion->id, 'status' => ProposalSuggestion::STATUS_REJECTED], ['class' => 'btn btn-sm btn-outline-secondary app-btn app-btn--ghost', 'data-method' => 'post']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="app-empty-state mt-4">
                        <h3 class="h6 mb-2">Nenhuma sugestão ainda</h3>
                        <p class="mb-0">As contribuições da comunidade aparecerão aqui assim que forem enviadas.</p>
                    </div>
                <?php endif; ?>
            </section>

            <section class="proposal-section">
                <div class="proposal-section__header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <span class="app-section-eyebrow">Discussão pública</span>
                        <h2 class="h4 mt-3 mb-0">Comentários</h2>
                    </div>
                    <span class="home-score-chip"><?= $totalComments ?></span>
                </div>

                <?php if (!Yii::$app->user->isGuest): ?>
                    <form id="proposal-comment-form" method="post" action="<?= Url::to(['/proposal-comment/create']) ?>" class="proposal-inline-form mt-4">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <input type="hidden" name="proposal_id" value="<?= (int) $model->id ?>">
                        <textarea class="form-control mb-3" name="content" rows="4" placeholder="Escreva um comentário"></textarea>
                        <button class="btn btn-primary app-btn" type="submit">Comentar</button>
                    </form>
                <?php endif; ?>

                <div class="proposal-thread mt-4">
                    <?php foreach ($rootComments as $comment): ?>
                        <?= $this->render('_comment', ['comment' => $comment, 'proposalId' => $model->id]) ?>
                    <?php endforeach; ?>
                    <?php if (empty($rootComments)): ?>
                        <div class="app-empty-state">
                            <h3 class="h6 mb-2">Nenhum comentário ainda</h3>
                            <p class="mb-0">Abra a discussão pública desta proposta e inicie a primeira conversa.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="proposal-side-card mb-4">
                <span class="app-section-eyebrow">Panorama</span>
                <h2 class="h5 mt-3 mb-3">Resumo executivo</h2>
                <div class="proposal-side-card__list">
                    <div><span>Candidato</span><strong><?= Html::encode((string) ($model->candidate->display_name ?? '-')) ?></strong></div>
                    <div><span>Eleição</span><strong><?= Html::encode((string) ($model->election->title ?? '-')) ?></strong></div>
                    <div><span>Tema</span><strong><?= Html::encode((string) ($model->theme ?: 'Tema geral')) ?></strong></div>
                    <div><span>Status</span><strong><?= Html::encode((string) $proposalStatusLabel) ?></strong></div>
                    <div><span>Edição</span><strong><?= $isEditLockedByDeadline ? 'Encerrada' : 'Disponível' ?></strong></div>
                </div>
            </section>

            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('postStatusUpdate', ['proposal' => $model])): ?>
                <section class="proposal-side-card mb-4">
                    <span class="app-section-eyebrow">Publicar avanço</span>
                    <h2 class="h5 mt-3 mb-3">Atualização de status</h2>
                    <?php $form = ActiveForm::begin(['id' => 'status-update-form', 'action' => ['/proposal-status-update/create']]); ?>
                    <?= Html::activeHiddenInput($statusModel, 'proposal_id') ?>
                    <?= $form->field($statusModel, 'status')->dropDownList(Proposal::statusOptions()) ?>
                    <?= $form->field($statusModel, 'update_date')->input('date') ?>
                    <?= $form->field($statusModel, 'description')->textInput(['maxlength' => true]) ?>
                    <?= Html::submitButton('Registrar atualização', ['class' => 'btn btn-outline-primary app-btn app-btn--ghost w-100']) ?>
                    <?php ActiveForm::end(); ?>
                </section>
            <?php endif; ?>

            <section class="proposal-side-card">
                <span class="app-section-eyebrow">Linha do tempo</span>
                <h2 class="h5 mt-3 mb-3">Acompanhamento pós-eleição</h2>
                <?php if (!empty($model->statusUpdates)): ?>
                    <div class="proposal-timeline">
                        <?php foreach ($model->statusUpdates as $update): ?>
                            <article class="proposal-timeline__item">
                                <span class="proposal-timeline__date"><?= Html::encode((string) $update->update_date) ?></span>
                                <h3><?= Html::encode((string) (Proposal::statusOptions()[$update->status] ?? $update->status)) ?></h3>
                                <p class="mb-0"><?= Html::encode((string) $update->description) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="app-empty-state">
                        <h3 class="h6 mb-2">Sem atualizações ainda</h3>
                        <p class="mb-0">Os avanços registrados pelo candidato aparecerão aqui em formato de linha do tempo.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>
