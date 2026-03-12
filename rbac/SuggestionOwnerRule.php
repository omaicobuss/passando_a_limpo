<?php

namespace app\rbac;

use app\models\Candidate;
use app\models\Proposal;
use app\models\ProposalSuggestion;

class SuggestionOwnerRule extends BaseOwnerRule
{
    public $name = 'isSuggestionOwnerCandidate';

    public function execute($user, $item, $params): bool
    {
        $userId = (int) $user;
        if ($userId <= 0) {
            return false;
        }

        if ($this->isAdmin($userId)) {
            return true;
        }

        $suggestion = $params['suggestion'] ?? null;
        if ($suggestion instanceof ProposalSuggestion) {
            $proposalId = (int) $suggestion->proposal_id;
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

        $suggestionId = (int) ($params['suggestion_id'] ?? $suggestion);
        if ($suggestionId <= 0) {
            return true;
        }

        $ownerId = (int) ProposalSuggestion::find()
            ->alias('s')
            ->select('c.user_id')
            ->innerJoin(['p' => Proposal::tableName()], 'p.id = s.proposal_id')
            ->innerJoin(['c' => Candidate::tableName()], 'c.id = p.candidate_id')
            ->where(['s.id' => $suggestionId])
            ->scalar();

        return $ownerId === $userId;
    }
}
