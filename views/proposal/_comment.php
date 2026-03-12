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
<div class="card mb-2 ms-<?= (int) ($comment->parent_id ? 4 : 0) ?>">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <strong><?= Html::encode($comment->user->username ?? 'Usuário') ?></strong>
                <?php if ($reportCount > 0): ?>
                    <span class="badge bg-warning text-dark">Inapropriado: <?= $reportCount ?></span>
                <?php endif; ?>
            </div>
            <small class="text-muted"><?= date('d/m/Y H:i', (int) $comment->created_at) ?></small>
        </div>

        <?php if ($comment->isDeleted()): ?>
            <p class="mb-2 text-muted fst-italic"><?= Html::encode($comment->getDisplayContent()) ?></p>
        <?php else: ?>
            <p class="mb-2"><?= nl2br(Html::encode($comment->getDisplayContent())) ?></p>
        <?php endif; ?>

        <?php if ($canDelete || $canReport): ?>
            <div class="d-flex gap-2 mb-2">
                <?php if ($canDelete): ?>
                    <form id="delete-comment-<?= (int) $comment->id ?>" method="post" action="<?= Url::to(['/proposal-comment/delete', 'id' => $comment->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                    </form>
                <?php endif; ?>

                <?php if ($canReport): ?>
                    <form id="report-comment-<?= (int) $comment->id ?>" method="post" action="<?= Url::to(['/proposal-comment/mark-inappropriate', 'id' => $comment->id]) ?>">
                        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                        <button class="btn btn-sm btn-outline-warning" type="submit" <?= $alreadyReported ? 'disabled' : '' ?>>
                            <?= $alreadyReported ? 'Já marcado' : 'Marcar como inapropriado' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!Yii::$app->user->isGuest && !$comment->isDeleted()): ?>
            <form method="post" action="<?= Url::to(['/proposal-comment/create']) ?>" class="mb-2">
                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
                <input type="hidden" name="proposal_id" value="<?= (int) $proposalId ?>">
                <input type="hidden" name="parent_id" value="<?= (int) $comment->id ?>">
                <div class="input-group input-group-sm">
                    <input class="form-control" name="content" placeholder="Responder comentário">
                    <button class="btn btn-outline-secondary" type="submit">Responder</button>
                </div>
            </form>
        <?php endif; ?>

        <?php foreach ($comment->children as $child): ?>
            <?= $this->render('_comment', ['comment' => $child, 'proposalId' => $proposalId]) ?>
        <?php endforeach; ?>
    </div>
</div>
