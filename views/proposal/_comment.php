<?php

/** @var app\models\ProposalComment $comment */
/** @var int $proposalId */

use yii\bootstrap5\Html;

?>
<div class="card mb-2 ms-<?= (int) ($comment->parent_id ? 4 : 0) ?>">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between">
            <strong><?= Html::encode($comment->user->username ?? 'Usuário') ?></strong>
            <small class="text-muted"><?= date('d/m/Y H:i', (int) $comment->created_at) ?></small>
        </div>
        <p class="mb-2"><?= nl2br(Html::encode($comment->content)) ?></p>

        <?php if (!Yii::$app->user->isGuest): ?>
            <form method="post" action="<?= yii\helpers\Url::to(['/proposal-comment/create']) ?>" class="mb-2">
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
