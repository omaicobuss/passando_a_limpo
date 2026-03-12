<?php

namespace app\rbac;

use app\models\Candidate;
use app\models\Proposal;

class ProposalOwnerRule extends BaseOwnerRule
{
    public $name = 'isProposalOwner';

    public function execute($user, $item, $params): bool
    {
        $userId = (int) $user;
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        $proposal = $params['proposal'] ?? null;
        if ($proposal instanceof Proposal) {
            $candidateId = (int) $proposal->candidate_id;
            if ($candidateId <= 0) {
                return false;
            }

            $ownerId = (int) Candidate::find()
                ->select('user_id')
                ->where(['id' => $candidateId])
                ->scalar();

            return $ownerId === $userId;
        }

        $proposalId = (int) ($params['proposal_id'] ?? $proposal);
        if ($proposalId <= 0) {
            return false;
        }

        $ownerId = (int) Proposal::find()
            ->alias('p')
            ->select('c.user_id')
            ->innerJoin(['c' => Candidate::tableName()], 'c.id = p.candidate_id')
            ->where(['p.id' => $proposalId])
            ->scalar();

        return $ownerId === $userId;
    }
}
