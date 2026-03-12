<?php

namespace app\rbac;

use app\models\Candidate;

class CandidateOwnerRule extends BaseOwnerRule
{
    public $name = 'isCandidateOwner';

    public function execute($user, $item, $params): bool
    {
        $userId = (int) $user;
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        $candidate = $params['candidate'] ?? null;
        if ($candidate instanceof Candidate) {
            return (int) $candidate->user_id === $userId;
        }

        $candidateId = (int) ($params['candidate_id'] ?? $candidate);
        if ($candidateId <= 0) {
            return false;
        }

        $ownerId = (int) Candidate::find()
            ->select('user_id')
            ->where(['id' => $candidateId])
            ->scalar();

        return $ownerId === $userId;
    }
}
