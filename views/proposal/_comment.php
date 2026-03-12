<?php

/** @var app\models\ProposalComment $comment */
/** @var int $proposalId */

use yii\bootstrap5\Html;
use yii\helpers\Url;

$currentUserId = Yii::$app->user->isGuest ? 0 : (int) Yii::$app->user->id;
$canDelete = $currentUserId > 0 && Yii::$app->user->can('deleteOwnComment', ['comment' => $comment]) && !$comment->isDeleted();
$canReport = $currentUserId > 0 && !$comment->isDeleted() && (int) $comment->user_id !== $currentUserId;
$alreadyReported = $canReport ? $comment->hasUserReported($currentUserId) : false;
$reportCount = (int) $comment->getReports()->count();
?>
<article class="proposal-comment-card<?= $comment->parent_id ? ' proposal-comment-card--child' : '' ?>">
    <div class="proposal-comment-card__surface">
        <div class="proposal-comment-card__header">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong><?= Html::encode($comment->user->username ?? 'Usuário') ?></strong>
                <?php if ($reportCount > 0): ?>
                    <span class="app-record-chip app-record-chip--accent">Inapropriado: <?= $reportCount ?></span>
                <?php endif; ?>
            </div>
            <small><?= date('d/m/Y H:i', (int) $comment->created_at) ?></small>
        </div>

        <div class="proposal-comment-card__body<?= $comment->isDeleted() ? ' proposal-comment-card__body--deleted' : '' ?>">
            <?= nl2br(Html::encode($comment->getDisplayContent())) ?>
        </div>

        <?php if ($canDelete || $canReport): ?>
            <div class="proposal-comment-card__actions">
                <?php if ($canDelete): ?>
                    <form id="delete-comment-<?= (int) $comment->id ?>" method="post" action="<?= Url::to(['/proposal-comment/delete', 'id' => $comment->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button class="btn btn-sm btn-outline-danger app-btn app-btn--ghost" type="submit">Excluir</button>
                    </form>
                <?php endif; ?>

                <?php if ($canReport): ?>
                    <form id="report-comment-<?= (int) $comment->id ?>" method="post" action="<?= Url::to(['/proposal-comment/mark-inappropriate', 'id' => $comment->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button class="btn btn-sm btn-outline-warning app-btn app-btn--ghost" type="submit" <?= $alreadyReported ? 'disabled' : '' ?>>
                            <?= $alreadyReported ? 'Já marcado' : 'Marcar como inapropriado' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!Yii::$app->user->isGuest && !$comment->isDeleted()): ?>
            <form method="post" action="<?= Url::to(['/proposal-comment/create']) ?>" class="proposal-comment-reply-form">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="proposal_id" value="<?= (int) $proposalId ?>">
                <input type="hidden" name="parent_id" value="<?= (int) $comment->id ?>">
                <div class="input-group input-group-sm">
                    <input class="form-control" name="content" placeholder="Responder comentário">
                    <button class="btn btn-outline-secondary app-btn app-btn--ghost" type="submit">Responder</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($comment->children)): ?>
        <div class="proposal-comment-card__children">
            <?php foreach ($comment->children as $child): ?>
                <?= $this->render('_comment', ['comment' => $child, 'proposalId' => $proposalId]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
