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
use yii\helpers\Url;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$latestRevision = $model->latestRevision;
$previousRevisions = array_slice($model->revisions, 1);
$isEditLockedByDeadline = $model->isEditLockedByElectionDeadline();
$currentProposalVoteValue = 0;
$currentSuggestionVoteMap = [];

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
<div class="proposal-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($model->title) ?></h1>
        <div class="d-flex gap-2">
            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('updateOwnProposal', ['proposal' => $model]) && !$isEditLockedByDeadline): ?>
                <?= Html::a('Editar proposta', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
            <?php endif; ?>
            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('admin') && !empty($previousRevisions)): ?>
                <?= Html::a('Histórico de versões', '#proposal-revision-history', ['class' => 'btn btn-outline-dark']) ?>
            <?php endif; ?>
            <?php if (!Yii::$app->user->isGuest): ?>
                <button class="btn <?= $currentProposalVoteValue === 1 ? 'btn-success active vote-selected' : 'btn-outline-success' ?> btn-vote" data-value="1" data-id="<?= (int) $model->id ?>" aria-pressed="<?= $currentProposalVoteValue === 1 ? 'true' : 'false' ?>">+1</button>
                <button class="btn <?= $currentProposalVoteValue === -1 ? 'btn-danger active vote-selected' : 'btn-outline-danger' ?> btn-vote" data-value="-1" data-id="<?= (int) $model->id ?>" aria-pressed="<?= $currentProposalVoteValue === -1 ? 'true' : 'false' ?>">-1</button>
            <?php endif; ?>
            <span class="badge bg-primary align-self-center fs-6">Score: <span id="proposal-score"><?= (int) $model->score ?></span></span>
        </div>
    </div>

    <?php if ($isEditLockedByDeadline): ?>
        <div class="alert alert-warning mb-3">
            Prazo da eleição encerrado: esta proposta não aceita mais edições.
        </div>
    <?php endif; ?>

    <?php if ($model->hasRevisionHistory() && $latestRevision !== null): ?>
        <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
            <div>
                <strong>Última edição registrada:</strong>
                <?= Html::encode(Yii::$app->formatter->asDatetime((int) $latestRevision->created_at, 'php:d/m/Y H:i')) ?>
                por <?= Html::encode($latestRevision->editor->username ?? 'sistema') ?>.
            </div>
            <span class="badge bg-info text-dark">Versão atual: <?= (int) $model->getCurrentVersionNumber() ?></span>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-2"><strong>Candidato:</strong> <?= Html::encode($model->candidate->display_name ?? '-') ?></p>
            <p class="mb-2"><strong>Eleição:</strong> <?= Html::encode($model->election->title ?? '-') ?></p>
            <p class="mb-2"><strong>Tema:</strong> <?= Html::encode((string) $model->theme) ?></p>
            <p class="mb-2"><strong>Status:</strong> <?= Html::encode(Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status) ?></p>
            <p class="mb-0"><?= nl2br(Html::encode($model->content)) ?></p>
        </div>
    </div>

    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('admin') && !empty($previousRevisions)): ?>
        <div class="card mb-3" id="proposal-revision-history">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Histórico de versões</span>
                <span class="badge bg-secondary"><?= count($previousRevisions) ?> anteriores</span>
            </div>
            <div class="accordion accordion-flush" id="proposal-revisions-accordion">
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
                                <p class="mb-2"><strong>Título:</strong> <?= Html::encode($revision->title) ?></p>
                                <p class="mb-2"><strong>Tema:</strong> <?= Html::encode((string) $revision->theme) ?></p>
                                <p class="mb-2"><strong>Status:</strong> <?= Html::encode(Proposal::statusOptions()[$revision->fulfillment_status] ?? $revision->fulfillment_status) ?></p>
                                <div class="border rounded p-3 bg-light-subtle"><?= nl2br(Html::encode($revision->content)) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('postStatusUpdate', ['proposal' => $model])): ?>
        <div class="card mb-3">
            <div class="card-header">Atualização de status</div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(['id' => 'status-update-form', 'action' => ['/proposal-status-update/create']]); ?>
                <?= Html::activeHiddenInput($statusModel, 'proposal_id') ?>
                <div class="row g-2">
                    <div class="col-md-3"><?= $form->field($statusModel, 'status')->dropDownList(Proposal::statusOptions()) ?></div>
                    <div class="col-md-3"><?= $form->field($statusModel, 'update_date')->input('date') ?></div>
                    <div class="col-md-6"><?= $form->field($statusModel, 'description')->textInput(['maxlength' => true]) ?></div>
                </div>
                <?= Html::submitButton('Registrar atualização', ['class' => 'btn btn-outline-primary']) ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-header">Acompanhamento pós-eleição</div>
        <ul class="list-group list-group-flush">
            <?php foreach ($model->statusUpdates as $update): ?>
                <li class="list-group-item">
                    <strong><?= Html::encode(Proposal::statusOptions()[$update->status] ?? $update->status) ?></strong>
                    <span class="text-muted"> em <?= Html::encode((string) $update->update_date) ?></span>
                    <div><?= Html::encode($update->description) ?></div>
                </li>
            <?php endforeach; ?>
            <?php if (empty($model->statusUpdates)): ?>
                <li class="list-group-item text-muted">Sem atualizações ainda.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-header">Sugestões</div>
        <div class="card-body">
            <?php if (!Yii::$app->user->isGuest): ?>
                <form id="proposal-suggestion-form" method="post" action="<?= Url::to(['/proposal-suggestion/create']) ?>" class="mb-4">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <input type="hidden" name="proposal_id" value="<?= (int) $model->id ?>">
                    <div class="row g-2">
                        <div class="col-md-4"><input class="form-control" name="title" placeholder="Título da sugestão"></div>
                        <div class="col-md-6"><input class="form-control" name="content" placeholder="Descreva sua sugestão"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Enviar</button></div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="list-group">
                <?php foreach ($model->suggestions as $suggestion): ?>
                    <?php $suggestionVoteValue = (int) ($currentSuggestionVoteMap[(int) $suggestion->id] ?? 0); ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <a href="<?= Url::to(['/proposal-suggestion/view', 'id' => $suggestion->id]) ?>">
                                <?= Html::encode($suggestion->title) ?>
                            </a>
                            <span class="badge bg-secondary"><?= Html::encode(ProposalSuggestion::statusOptions()[$suggestion->status] ?? $suggestion->status) ?></span>
                        </div>
                        <p class="mb-2"><?= Html::encode($suggestion->content) ?></p>
                        <div class="d-flex gap-2 align-items-center">
                            <?php if (!Yii::$app->user->isGuest): ?>
                                <button class="btn btn-sm <?= $suggestionVoteValue === 1 ? 'btn-success active vote-selected' : 'btn-outline-success' ?> btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="1" aria-pressed="<?= $suggestionVoteValue === 1 ? 'true' : 'false' ?>">+1</button>
                                <button class="btn btn-sm <?= $suggestionVoteValue === -1 ? 'btn-danger active vote-selected' : 'btn-outline-danger' ?> btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="-1" aria-pressed="<?= $suggestionVoteValue === -1 ? 'true' : 'false' ?>">-1</button>
                            <?php endif; ?>
                            <span>Score: <strong id="suggestion-score-<?= (int) $suggestion->id ?>"><?= $suggestion->getScore() ?></strong></span>
                            <?php if (!Yii::$app->user->isGuest && Yii::$app->user->can('moderateSuggestion', ['suggestion' => $suggestion])): ?>
                                <?= Html::a('Aprovar', ['/proposal-suggestion/moderate', 'id' => $suggestion->id, 'status' => ProposalSuggestion::STATUS_APPROVED], ['class' => 'btn btn-sm btn-outline-primary', 'data-method' => 'post']) ?>
                                <?= Html::a('Rejeitar', ['/proposal-suggestion/moderate', 'id' => $suggestion->id, 'status' => ProposalSuggestion::STATUS_REJECTED], ['class' => 'btn btn-sm btn-outline-secondary', 'data-method' => 'post']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($model->suggestions)): ?>
                    <div class="list-group-item text-muted">Nenhuma sugestão.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Comentários</div>
        <div class="card-body">
            <?php if (!Yii::$app->user->isGuest): ?>
                <form id="proposal-comment-form" method="post" action="<?= Url::to(['/proposal-comment/create']) ?>" class="mb-4">
                    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                    <input type="hidden" name="proposal_id" value="<?= (int) $model->id ?>">
                    <textarea class="form-control mb-2" name="content" rows="3" placeholder="Escreva um comentário"></textarea>
                    <button class="btn btn-primary btn-sm">Comentar</button>
                </form>
            <?php endif; ?>

            <?php foreach ($rootComments as $comment): ?>
                <?= $this->render('_comment', ['comment' => $comment, 'proposalId' => $model->id]) ?>
            <?php endforeach; ?>
            <?php if (empty($rootComments)): ?>
                <p class="text-muted mb-0">Nenhum comentário ainda.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$voteUrl = Url::to(['/proposal/vote']);
$suggestionVoteUrl = Url::to(['/proposal-suggestion/vote']);
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$this->registerCss(<<<CSS
.vote-selected {
    box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.3);
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
?>
