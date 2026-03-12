<?php

namespace app\rbac;

use app\models\ProposalComment;

class CommentOwnerRule extends BaseOwnerRule
{
    public $name = 'isCommentOwner';

    public function execute($user, $item, $params): bool
    {
        $userId = (int) $user;
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        $comment = $params['comment'] ?? null;
        if ($comment instanceof ProposalComment) {
            return (int) $comment->user_id === $userId;
        }

        $commentId = (int) ($params['comment_id'] ?? $comment);
        if ($commentId <= 0) {
            return false;
        }

        $ownerId = (int) ProposalComment::find()
            ->select('user_id')
            ->where(['id' => $commentId])
            ->scalar();

        return $ownerId === $userId;
    }
}
