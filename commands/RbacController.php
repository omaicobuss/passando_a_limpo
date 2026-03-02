<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class RbacController extends Controller
{
    public function actionInit(): int
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();

        $createProposal = $auth->createPermission('createProposal');
        $createProposal->description = 'Create proposal';
        $auth->add($createProposal);

        $updateOwnProposal = $auth->createPermission('updateOwnProposal');
        $updateOwnProposal->description = 'Update own proposal';
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

        $moderateSuggestion = $auth->createPermission('moderateSuggestion');
        $moderateSuggestion->description = 'Moderate proposal suggestion';
        $auth->add($moderateSuggestion);

        $manageElection = $auth->createPermission('manageElection');
        $manageElection->description = 'Create, update and delete elections';
        $auth->add($manageElection);

        $manageCandidate = $auth->createPermission('manageCandidate');
        $manageCandidate->description = 'Manage candidates';
        $auth->add($manageCandidate);

        $postStatusUpdate = $auth->createPermission('postStatusUpdate');
        $postStatusUpdate->description = 'Post proposal status update';
        $auth->add($postStatusUpdate);

        $citizen = $auth->createRole('citizen');
        $auth->add($citizen);
        $auth->addChild($citizen, $voteProposal);
        $auth->addChild($citizen, $commentProposal);

        $candidate = $auth->createRole('candidate');
        $auth->add($candidate);
        $auth->addChild($candidate, $citizen);
        $auth->addChild($candidate, $createProposal);
        $auth->addChild($candidate, $updateOwnProposal);
        $auth->addChild($candidate, $postStatusUpdate);
        $auth->addChild($candidate, $moderateSuggestion);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $candidate);
        $auth->addChild($admin, $deleteProposal);
        $auth->addChild($admin, $manageElection);
        $auth->addChild($admin, $manageCandidate);

        $adminUser = User::findOne(['username' => 'admin']);
        if ($adminUser !== null) {
            $auth->assign($admin, $adminUser->id);
        }

        $this->stdout("RBAC initialized.\n");
        return ExitCode::OK;
    }
}
