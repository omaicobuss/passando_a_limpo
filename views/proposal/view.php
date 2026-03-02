<?php

/** @var yii\web\View $this */
/** @var app\models\Proposal $model */
/** @var app\models\ProposalComment $commentModel */
/** @var app\models\ProposalSuggestion $suggestionModel */
/** @var app\models\ProposalStatusUpdate $statusModel */
/** @var app\models\ProposalComment[] $rootComments */

use app\models\Proposal;
use app\models\ProposalSuggestion;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Propostas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="proposal-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 m-0"><?= Html::encode($model->title) ?></h1>
        <div class="d-flex gap-2">
            <?php if (!Yii::$app->user->isGuest): ?>
                <button class="btn btn-success btn-vote" data-value="1" data-id="<?= (int) $model->id ?>">+1</button>
                <button class="btn btn-danger btn-vote" data-value="-1" data-id="<?= (int) $model->id ?>">-1</button>
            <?php endif; ?>
            <span class="badge bg-primary align-self-center fs-6">Score: <span id="proposal-score"><?= (int) $model->score ?></span></span>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-2"><strong>Candidato:</strong> <?= Html::encode($model->candidate->display_name ?? '-') ?></p>
            <p class="mb-2"><strong>Eleição:</strong> <?= Html::encode($model->election->title ?? '-') ?></p>
            <p class="mb-2"><strong>Tema:</strong> <?= Html::encode((string) $model->theme) ?></p>
            <p class="mb-2"><strong>Status:</strong> <?= Html::encode(Proposal::statusOptions()[$model->fulfillment_status] ?? $model->fulfillment_status) ?></p>
            <p class="mb-0"><?= nl2br(Html::encode($model->content)) ?></p>
        </div>
    </div>

    <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->identity->isAdmin() || (int) $model->candidate->user_id === (int) Yii::$app->user->id)): ?>
        <div class="card mb-3">
            <div class="card-header">Atualização de status</div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(['action' => ['/proposal-status-update/create']]); ?>
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
                <form method="post" action="<?= Url::to(['/proposal-suggestion/create']) ?>" class="mb-4">
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
                                <button class="btn btn-sm btn-outline-success btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="1">+1</button>
                                <button class="btn btn-sm btn-outline-danger btn-suggestion-vote" data-id="<?= (int) $suggestion->id ?>" data-value="-1">-1</button>
                            <?php endif; ?>
                            <span>Score: <strong id="suggestion-score-<?= (int) $suggestion->id ?>"><?= $suggestion->getScore() ?></strong></span>
                            <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->identity->isAdmin() || (int) $model->candidate->user_id === (int) Yii::$app->user->id)): ?>
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
                <form method="post" action="<?= Url::to(['/proposal-comment/create']) ?>" class="mb-4">
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
$this->registerJs(<<<JS
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
      } else {
        alert(data.message || 'Falha ao votar');
      }
    });
  });
});
JS);
?>
