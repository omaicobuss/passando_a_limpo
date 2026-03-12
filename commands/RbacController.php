<?php

namespace app\commands;

use app\models\User;
use app\rbac\CandidateOwnerRule;
use app\rbac\CandidateRequestOwnerRule;
use app\rbac\CommentOwnerRule;
use app\rbac\ProposalOwnerRule;
use app\rbac\SuggestionOwnerRule;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class RbacController extends Controller
{
    public function actionInit(): int
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();

        $candidateOwnerRule = new CandidateOwnerRule();
        $auth->add($candidateOwnerRule);

        $proposalOwnerRule = new ProposalOwnerRule();
        $auth->add($proposalOwnerRule);

        $suggestionOwnerRule = new SuggestionOwnerRule();
        $auth->add($suggestionOwnerRule);

        $commentOwnerRule = new CommentOwnerRule();
        $auth->add($commentOwnerRule);

        $candidateRequestOwnerRule = new CandidateRequestOwnerRule();
        $auth->add($candidateRequestOwnerRule);

        $createProposal = $auth->createPermission('createProposal');
        $createProposal->description = 'Create proposal';
        $createProposal->ruleName = $proposalOwnerRule->name;
        $auth->add($createProposal);

        $updateOwnProposal = $auth->createPermission('updateOwnProposal');
        $updateOwnProposal->description = 'Update own proposal';
        $updateOwnProposal->ruleName = $proposalOwnerRule->name;
        $auth->add($updateOwnProposal);

        $deleteProposal = $auth->createPermission('deleteProposal');
        $deleteProposal->description = 'Delete proposal';
        $auth->add($deleteProposal);

        $voteProposal = $auth->createPermission('voteProposal');
        $voteProposal->description = 'Vote proposal';
        $auth->add($voteProposal);

        $commentProposal = $auth->createPermission('commentProposal');
        $commentProposal->description = 'Comment proposal';
        $auth->add($commentProposal);

        $deleteOwnComment = $auth->createPermission('deleteOwnComment');
        $deleteOwnComment->description = 'Delete own comment';
        $deleteOwnComment->ruleName = $commentOwnerRule->name;
        $auth->add($deleteOwnComment);

        $moderateSuggestion = $auth->createPermission('moderateSuggestion');
        $moderateSuggestion->description = 'Moderate proposal suggestion';
        $moderateSuggestion->ruleName = $suggestionOwnerRule->name;
        $auth->add($moderateSuggestion);

        $manageElection = $auth->createPermission('manageElection');
        $manageElection->description = 'Create, update and delete elections';
        $auth->add($manageElection);

        $manageCandidate = $auth->createPermission('manageCandidate');
        $manageCandidate->description = 'Manage candidates';
        $manageCandidate->ruleName = $candidateOwnerRule->name;
        $auth->add($manageCandidate);

        $postStatusUpdate = $auth->createPermission('postStatusUpdate');
        $postStatusUpdate->description = 'Post proposal status update';
        $postStatusUpdate->ruleName = $proposalOwnerRule->name;
        $auth->add($postStatusUpdate);

        $viewCandidateRequestDocument = $auth->createPermission('viewCandidateRequestDocument');
        $viewCandidateRequestDocument->description = 'View own candidate request document';
        $viewCandidateRequestDocument->ruleName = $candidateRequestOwnerRule->name;
        $auth->add($viewCandidateRequestDocument);

        $citizen = $auth->createRole('citizen');
        $auth->add($citizen);
        $auth->addChild($citizen, $voteProposal);
        $auth->addChild($citizen, $commentProposal);
        $auth->addChild($citizen, $deleteOwnComment);
        $auth->addChild($citizen, $viewCandidateRequestDocument);

        $candidate = $auth->createRole('candidate');
        $auth->add($candidate);
        $auth->addChild($candidate, $citizen);
        $auth->addChild($candidate, $manageCandidate);
        $auth->addChild($candidate, $createProposal);
        $auth->addChild($candidate, $updateOwnProposal);
        $auth->addChild($candidate, $postStatusUpdate);
        $auth->addChild($candidate, $moderateSuggestion);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $candidate);
        $auth->addChild($admin, $deleteProposal);
        $auth->addChild($admin, $manageElection);

        $this->assignRolesFromUserTable();

        $this->stdout("RBAC initialized.\n");
        return ExitCode::OK;
    }

    public function actionSyncUsers(): int
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            $this->stderr("AuthManager nao configurado.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->assignRolesFromUserTable();
        $this->stdout("RBAC users synced.\n");
        return ExitCode::OK;
    }

    private function assignRolesFromUserTable(): void
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            return;
        }

        foreach (User::find()->select(['id', 'role'])->all() as $user) {
            $roleName = in_array((string) $user->role, ['admin', 'candidate', 'citizen'], true)
                ? (string) $user->role
                : 'citizen';

            $role = $auth->getRole($roleName);
            if ($role === null) {
                continue;
            }

            $auth->revokeAll((int) $user->id);
            $auth->assign($role, (int) $user->id);
        }
    }
}
