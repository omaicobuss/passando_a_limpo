<?php

namespace app\rbac;

use app\models\CandidateUpgradeRequest;

class CandidateRequestOwnerRule extends BaseOwnerRule
{
    public $name = 'isCandidateRequestOwner';

    public function execute($user, $item, $params): bool
    {
        $userId = (int) $user;
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        $request = $params['candidateRequest'] ?? null;
        if ($request instanceof CandidateUpgradeRequest) {
            return (int) $request->user_id === $userId;
        }

        $requestId = (int) ($params['candidate_request_id'] ?? $request);
        if ($requestId <= 0) {
            return true;
        }

        $ownerId = (int) CandidateUpgradeRequest::find()
            ->select('user_id')
            ->where(['id' => $requestId])
            ->scalar();

        return $ownerId === $userId;
    }
}
