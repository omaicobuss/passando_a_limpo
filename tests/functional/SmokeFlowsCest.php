<?php

use app\models\Candidate;
use app\models\CandidateUpgradeRequest;
use app\models\Election;
use app\models\Proposal;
use app\models\ProposalComment;
use app\models\ProposalCommentReport;
use app\models\ProposalRevision;
use app\models\ProposalSuggestion;
use app\models\ProposalStatusUpdate;
use app\models\User;
use app\rbac\CandidateOwnerRule;
use app\rbac\CandidateRequestOwnerRule;
use app\rbac\CommentOwnerRule;
use app\rbac\ProposalOwnerRule;
use app\rbac\SuggestionOwnerRule;

class SmokeFlowsCest
{
    public function _before(FunctionalTester $I): void
    {
        $this->resetCoreData();
        $this->seedRbac();
    }

    public function signupCreatesCitizenUser(FunctionalTester $I): void
    {
        $suffix = (string) time();
        $username = 'smoke_citizen_' . $suffix;
        $email = $username . '@example.com';
        $password = 'Senha123!';

        $I->amOnRoute('site/signup');
        $I->submitForm('form', [
            'SignupForm[username]' => $username,
            'SignupForm[email]' => $email,
            'SignupForm[password]' => $password,
        ]);

        $I->see('Conta criada com sucesso. Faça login para continuar.');
        $I->seeRecord(User::class, [
            'username' => $username,
            'email' => $email,
            'role' => 'citizen',
        ]);
    }

    public function loginWithValidCredentials(FunctionalTester $I): void
    {
        $user = $this->createUser('smoke_login_user', 'smoke_login_user@example.com', 'Senha123!', 'citizen');

        $I->amOnRoute('site/login');
        $I->submitForm('#login-form', [
            'LoginForm[username]' => $user->username,
            'LoginForm[password]' => 'Senha123!',
        ]);

        $I->see('Sair (' . $user->username . ')');
    }

    public function citizenCanRequestCandidateUpgradeWithDocument(FunctionalTester $I): void
    {
        $user = $this->createUser('smoke_request_user', 'smoke_request_user@example.com', 'Senha123!', 'citizen');

        $I->amLoggedInAs($user);
        $I->amOnRoute('site/my-account');

        $I->attachFile('input[type="file"][name="CandidateUpgradeRequestForm[document]"]', 'sample-document.pdf');
        $I->fillField('textarea[name="CandidateUpgradeRequestForm[message]"]', 'Solicitacao de candidatura via smoke test.');
        $I->click('Enviar solicitacao');

        $I->see('Solicitacao enviada com sucesso. Aguarde a analise de um administrador.');
        $I->seeRecord(CandidateUpgradeRequest::class, [
            'user_id' => $user->id,
            'status' => CandidateUpgradeRequest::STATUS_PENDING,
        ]);

        $request = CandidateUpgradeRequest::find()->where(['user_id' => $user->id])->orderBy(['id' => SORT_DESC])->one();
        $I->assertNotNull($request);
        $I->assertNotSame('', (string) $request->document_path);
    }

    public function candidateCannotEditOtherCandidate(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUserA = $this->createUser('smoke_cand_a', 'smoke_cand_a@example.com', 'Senha123!', 'candidate');
        $candidateUserB = $this->createUser('smoke_cand_b', 'smoke_cand_b@example.com', 'Senha123!', 'candidate');

        $candidateA = $this->createCandidate($candidateUserA, $election, 'Candidato A');
        $candidateB = $this->createCandidate($candidateUserB, $election, 'Candidato B');

        $I->assertNotNull($candidateA);
        $I->assertNotNull($candidateB);

        $I->amLoggedInAs($candidateUserA);
        $I->amOnRoute('candidate/update', ['id' => $candidateB->id]);

        $I->seeResponseCodeIs(403);
        $I->see('Você não pode alterar este candidato.');
    }

    public function candidateCannotEditOtherCandidateWithPermissiveManagePermission(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUserA = $this->createUser('smoke_cand_perm_a', 'smoke_cand_perm_a@example.com', 'Senha123!', 'candidate');
        $candidateUserB = $this->createUser('smoke_cand_perm_b', 'smoke_cand_perm_b@example.com', 'Senha123!', 'candidate');

        $this->createCandidate($candidateUserA, $election, 'Candidato permissao A');
        $candidateB = $this->createCandidate($candidateUserB, $election, 'Candidato permissao B');

        $auth = \Yii::$app->authManager;
        $candidateRole = $auth->getRole('candidate');
        $manageCandidate = $auth->getPermission('manageCandidate');

        $I->assertNotNull($candidateRole);
        $I->assertNotNull($manageCandidate);

        $auth->removeChild($candidateRole, $manageCandidate);
        $auth->remove($manageCandidate);

        $permissiveManageCandidate = $auth->createPermission('manageCandidate');
        $permissiveManageCandidate->description = 'Manage candidates without ownership rule';
        $auth->add($permissiveManageCandidate);
        $auth->addChild($candidateRole, $permissiveManageCandidate);

        $I->amLoggedInAs($candidateUserA);
        $I->amOnRoute('candidate/update', ['id' => $candidateB->id]);

        $I->seeResponseCodeIs(403);
        $I->see('Você não pode alterar este candidato.');
    }

    public function candidateCannotEditOtherProposal(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUserA = $this->createUser('smoke_prop_a', 'smoke_prop_a@example.com', 'Senha123!', 'candidate');
        $candidateUserB = $this->createUser('smoke_prop_b', 'smoke_prop_b@example.com', 'Senha123!', 'candidate');

        $candidateB = $this->createCandidate($candidateUserB, $election, 'Candidato dono da proposta');
        $this->createCandidate($candidateUserA, $election, 'Candidato sem posse');

        $proposal = new Proposal([
            'election_id' => $election->id,
            'candidate_id' => $candidateB->id,
            'title' => 'Proposta privada de B',
            'theme' => 'Saude',
            'content' => 'Conteudo de teste.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposal->save(), json_encode($proposal->getErrors(), JSON_UNESCAPED_UNICODE));

        $I->amLoggedInAs($candidateUserA);
        $I->amOnRoute('proposal/update', ['id' => $proposal->id]);

        $I->seeResponseCodeIs(403);
        $I->see('Você não pode editar esta proposta.');
    }

    public function candidateCanEditOwnProposalAndAdminCanReviewPreviousVersions(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUser = $this->createUser('smoke_prop_owner', 'smoke_prop_owner@example.com', 'Senha123!', 'candidate');
        $candidate = $this->createCandidate($candidateUser, $election, 'Candidato dono');

        $proposal = new Proposal([
            'election_id' => $election->id,
            'candidate_id' => $candidate->id,
            'title' => 'Proposta original',
            'theme' => 'Mobilidade',
            'content' => 'Conteúdo original da proposta.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposal->save(), json_encode($proposal->getErrors(), JSON_UNESCAPED_UNICODE));

        $I->amLoggedInAs($candidateUser);
        $I->amOnRoute('proposal/update', ['id' => $proposal->id]);
        $I->submitForm('#proposal-form', [
            'Proposal[title]' => 'Proposta revisada',
            'Proposal[theme]' => 'Educação',
            'Proposal[election_id]' => $election->id,
            'Proposal[candidate_id]' => $candidate->id,
            'Proposal[content]' => 'Conteúdo revisado da proposta.',
            'Proposal[fulfillment_status]' => Proposal::FULFILLMENT_IN_PROGRESS,
        ]);

        $I->see('Proposta atualizada.');
        $I->see('Última edição registrada:');
        $I->see('Versão atual: 2');
        $I->see('Proposta revisada');
        $I->seeRecord(ProposalRevision::class, [
            'proposal_id' => $proposal->id,
            'version_number' => 1,
            'title' => 'Proposta original',
            'content' => 'Conteúdo original da proposta.',
        ]);
        $I->seeRecord(ProposalRevision::class, [
            'proposal_id' => $proposal->id,
            'version_number' => 2,
            'title' => 'Proposta revisada',
            'content' => 'Conteúdo revisado da proposta.',
            'edited_by_user_id' => $candidateUser->id,
        ]);

        $admin = $this->createUser('smoke_prop_admin', 'smoke_prop_admin@example.com', 'Senha123!', 'admin');
        $I->amLoggedInAs($admin);
        $I->amOnRoute('proposal/view', ['id' => $proposal->id]);

        $I->see('Histórico de versões');
        $I->see('Versão 1');
        $I->see('Conteúdo original da proposta.');
    }

    public function userCanDeleteOwnCommentAndOtherUserCanMarkAsInappropriate(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUser = $this->createUser('smoke_comment_cand', 'smoke_comment_cand@example.com', 'Senha123!', 'candidate');
        $candidate = $this->createCandidate($candidateUser, $election, 'Candidato de comentários');

        $proposal = new Proposal([
            'election_id' => $election->id,
            'candidate_id' => $candidate->id,
            'title' => 'Proposta para comentários',
            'theme' => 'Transparencia',
            'content' => 'Conteúdo base para testes de comentários.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposal->save(), json_encode($proposal->getErrors(), JSON_UNESCAPED_UNICODE));

        $author = $this->createUser('smoke_comment_author', 'smoke_comment_author@example.com', 'Senha123!', 'citizen');
        $otherUser = $this->createUser('smoke_comment_other', 'smoke_comment_other@example.com', 'Senha123!', 'citizen');

        $commentToDelete = new ProposalComment([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'content' => 'Comentário que será removido pelo autor.',
        ]);
        $I->assertTrue($commentToDelete->save(), json_encode($commentToDelete->getErrors(), JSON_UNESCAPED_UNICODE));

        $commentToReport = new ProposalComment([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'content' => 'Comentário que será marcado como inapropriado.',
        ]);
        $I->assertTrue($commentToReport->save(), json_encode($commentToReport->getErrors(), JSON_UNESCAPED_UNICODE));

        $commentToModerateDelete = new ProposalComment([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'content' => 'Comentário para moderação por exclusão.',
        ]);
        $I->assertTrue($commentToModerateDelete->save(), json_encode($commentToModerateDelete->getErrors(), JSON_UNESCAPED_UNICODE));

        $I->amLoggedInAs($author);
        $I->amOnRoute('proposal/view', ['id' => $proposal->id]);
        $I->click(['css' => '#delete-comment-' . $commentToDelete->id . ' button']);

        $I->see('Comentário removido.');
        $I->see('Comentário excluído pelo autor.');
        $I->seeRecord(ProposalComment::class, [
            'id' => $commentToDelete->id,
            'is_deleted' => 1,
            'deleted_by_user_id' => $author->id,
        ]);

        $I->amLoggedInAs($otherUser);
        $I->amOnRoute('proposal/view', ['id' => $proposal->id]);
        $I->click(['css' => '#report-comment-' . $commentToReport->id . ' button']);

        $I->click(['css' => '#report-comment-' . $commentToModerateDelete->id . ' button']);

        $I->see('Comentário marcado como inapropriado.');
        $I->seeRecord(ProposalCommentReport::class, [
            'comment_id' => $commentToReport->id,
            'user_id' => $otherUser->id,
        ]);
        $I->seeRecord(ProposalCommentReport::class, [
            'comment_id' => $commentToModerateDelete->id,
            'user_id' => $otherUser->id,
        ]);

        $admin = $this->createUser('smoke_comment_admin', 'smoke_comment_admin@example.com', 'Senha123!', 'admin');
        $I->amLoggedInAs($admin);
        $I->amOnRoute('proposal-comment/reported');

        $I->see('Comentários inapropriados');
        $I->see('Comentário que será marcado como inapropriado.');
        $I->see('Comentário para moderação por exclusão.');

        $I->click(['css' => '#moderate-keep-comment-' . $commentToReport->id . ' button']);
        $I->see('Denúncias arquivadas para o comentário.');
        $I->dontSeeRecord(ProposalCommentReport::class, [
            'comment_id' => $commentToReport->id,
            'user_id' => $otherUser->id,
        ]);

        $I->click(['css' => '#moderate-delete-comment-' . $commentToModerateDelete->id . ' button']);
        $I->see('Comentário removido.');
        $I->seeRecord(ProposalComment::class, [
            'id' => $commentToModerateDelete->id,
            'is_deleted' => 1,
            'deleted_by_user_id' => $admin->id,
        ]);
        $I->dontSeeRecord(ProposalCommentReport::class, [
            'comment_id' => $commentToModerateDelete->id,
            'user_id' => $otherUser->id,
        ]);

        $I->amOnRoute('proposal/view', ['id' => $proposal->id]);
        $I->see('Comentário excluído devido a denúncias de conteúdo inapropriado.');
    }

    public function candidateCanPostOwnStatusUpdateAndCannotPostForOthers(FunctionalTester $I): void
    {
        $election = $this->createElection();

        $candidateUserA = $this->createUser('smoke_status_owner', 'smoke_status_owner@example.com', 'Senha123!', 'candidate');
        $candidateUserB = $this->createUser('smoke_status_other', 'smoke_status_other@example.com', 'Senha123!', 'candidate');

        $candidateA = $this->createCandidate($candidateUserA, $election, 'Candidato dono do status');
        $candidateB = $this->createCandidate($candidateUserB, $election, 'Candidato sem posse');

        $proposalA = new Proposal([
            'election_id' => $election->id,
            'candidate_id' => $candidateA->id,
            'title' => 'Proposta para status update (A)',
            'theme' => 'Saúde',
            'content' => 'Conteúdo base A.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposalA->save(), json_encode($proposalA->getErrors(), JSON_UNESCAPED_UNICODE));

        $proposalB = new Proposal([
            'election_id' => $election->id,
            'candidate_id' => $candidateB->id,
            'title' => 'Proposta para status update (B)',
            'theme' => 'Educação',
            'content' => 'Conteúdo base B.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposalB->save(), json_encode($proposalB->getErrors(), JSON_UNESCAPED_UNICODE));

        $I->amLoggedInAs($candidateUserA);
        $I->amOnRoute('proposal/view', ['id' => $proposalA->id]);
        $I->submitForm('#status-update-form', [
            'ProposalStatusUpdate[proposal_id]' => $proposalA->id,
            'ProposalStatusUpdate[status]' => Proposal::FULFILLMENT_IN_PROGRESS,
            'ProposalStatusUpdate[update_date]' => date('Y-m-d'),
            'ProposalStatusUpdate[description]' => 'Atualização válida do dono.',
        ]);

        $I->see('Atualização registrada.');
        $I->seeRecord(ProposalStatusUpdate::class, [
            'proposal_id' => $proposalA->id,
            'user_id' => $candidateUserA->id,
            'status' => Proposal::FULFILLMENT_IN_PROGRESS,
            'description' => 'Atualização válida do dono.',
        ]);
        $I->seeRecord(Proposal::class, [
            'id' => $proposalA->id,
            'fulfillment_status' => Proposal::FULFILLMENT_IN_PROGRESS,
        ]);

        $I->amOnRoute('proposal/view', ['id' => $proposalA->id]);
        $I->submitForm('#status-update-form', [
            'ProposalStatusUpdate[proposal_id]' => $proposalB->id,
            'ProposalStatusUpdate[status]' => Proposal::FULFILLMENT_COMPLETED,
            'ProposalStatusUpdate[update_date]' => date('Y-m-d'),
            'ProposalStatusUpdate[description]' => 'Tentativa indevida em proposta alheia.',
        ]);

        $I->seeResponseCodeIs(403);
        $I->see('Você não pode atualizar essa proposta.');
    }

    public function proposalCannotBeEditedAfterDeadlineButAllowsSuggestionCommentAndStatusUpdate(FunctionalTester $I): void
    {
        $endedElection = new Election([
            'title' => 'Eleição encerrada para bloqueio de edição',
            'description' => 'Eleição usada para validar regra pós-prazo.',
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d', strtotime('-1 day')),
        ]);
        $I->assertTrue($endedElection->save(), json_encode($endedElection->getErrors(), JSON_UNESCAPED_UNICODE));

        $candidateUser = $this->createUser('smoke_deadline_owner', 'smoke_deadline_owner@example.com', 'Senha123!', 'candidate');
        $candidate = $this->createCandidate($candidateUser, $endedElection, 'Candidato pós-prazo');

        $proposal = new Proposal([
            'election_id' => $endedElection->id,
            'candidate_id' => $candidate->id,
            'title' => 'Proposta bloqueada por prazo',
            'theme' => 'Planejamento',
            'content' => 'Conteúdo inicial da proposta pós-prazo.',
            'fulfillment_status' => Proposal::FULFILLMENT_NOT_STARTED,
        ]);
        $I->assertTrue($proposal->save(), json_encode($proposal->getErrors(), JSON_UNESCAPED_UNICODE));

        $I->amLoggedInAs($candidateUser);
        $I->amOnRoute('proposal/update', ['id' => $proposal->id]);

        $I->seeResponseCodeIs(403);
        $I->see('Não é permitido editar proposta após o prazo da eleição.');

        $I->amOnRoute('proposal/view', ['id' => $proposal->id]);
        $I->dontSeeLink('Editar proposta');
        $I->see('Prazo da eleição encerrado: esta proposta não aceita mais edições.');

        $I->submitForm('#proposal-comment-form', [
            'proposal_id' => $proposal->id,
            'content' => 'Comentário permitido após prazo.',
        ]);

        $I->see('Comentário publicado.');
        $I->seeRecord(ProposalComment::class, [
            'proposal_id' => $proposal->id,
            'user_id' => $candidateUser->id,
            'content' => 'Comentário permitido após prazo.',
        ]);

        $I->submitForm('#proposal-suggestion-form', [
            'proposal_id' => $proposal->id,
            'title' => 'Sugestão permitida após prazo',
            'content' => 'Texto de sugestão no pós-prazo.',
        ]);

        $I->see('Sugestão enviada.');
        $I->seeRecord(ProposalSuggestion::class, [
            'proposal_id' => $proposal->id,
            'user_id' => $candidateUser->id,
            'title' => 'Sugestão permitida após prazo',
            'content' => 'Texto de sugestão no pós-prazo.',
        ]);

        $I->submitForm('#status-update-form', [
            'ProposalStatusUpdate[proposal_id]' => $proposal->id,
            'ProposalStatusUpdate[status]' => Proposal::FULFILLMENT_IN_PROGRESS,
            'ProposalStatusUpdate[update_date]' => date('Y-m-d'),
            'ProposalStatusUpdate[description]' => 'Status permitido após prazo.',
        ]);

        $I->see('Atualização registrada.');
        $I->seeRecord(ProposalStatusUpdate::class, [
            'proposal_id' => $proposal->id,
            'user_id' => $candidateUser->id,
            'status' => Proposal::FULFILLMENT_IN_PROGRESS,
            'description' => 'Status permitido após prazo.',
        ]);
    }

    private function resetCoreData(): void
    {
        CandidateUpgradeRequest::deleteAll();
        ProposalCommentReport::deleteAll();
        ProposalComment::deleteAll();
        ProposalStatusUpdate::deleteAll();
        ProposalRevision::deleteAll();
        Proposal::deleteAll();
        Candidate::deleteAll();
        Election::deleteAll();

        User::deleteAll(['like', 'username', 'smoke_%', false]);
        User::deleteAll(['username' => 'admin']);
    }

    private function seedRbac(): void
    {
        $auth = \Yii::$app->authManager;
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
        $createProposal->ruleName = $proposalOwnerRule->name;
        $auth->add($createProposal);

        $updateOwnProposal = $auth->createPermission('updateOwnProposal');
        $updateOwnProposal->ruleName = $proposalOwnerRule->name;
        $auth->add($updateOwnProposal);

        $deleteProposal = $auth->createPermission('deleteProposal');
        $auth->add($deleteProposal);

        $voteProposal = $auth->createPermission('voteProposal');
        $auth->add($voteProposal);

        $commentProposal = $auth->createPermission('commentProposal');
        $auth->add($commentProposal);

        $deleteOwnComment = $auth->createPermission('deleteOwnComment');
        $deleteOwnComment->ruleName = $commentOwnerRule->name;
        $auth->add($deleteOwnComment);

        $moderateSuggestion = $auth->createPermission('moderateSuggestion');
        $moderateSuggestion->ruleName = $suggestionOwnerRule->name;
        $auth->add($moderateSuggestion);

        $manageElection = $auth->createPermission('manageElection');
        $auth->add($manageElection);

        $manageCandidate = $auth->createPermission('manageCandidate');
        $manageCandidate->ruleName = $candidateOwnerRule->name;
        $auth->add($manageCandidate);

        $postStatusUpdate = $auth->createPermission('postStatusUpdate');
        $postStatusUpdate->ruleName = $proposalOwnerRule->name;
        $auth->add($postStatusUpdate);

        $viewCandidateRequestDocument = $auth->createPermission('viewCandidateRequestDocument');
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
    }

    private function createUser(string $username, string $email, string $password, string $role): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->role = $role;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;

        if (!$user->save()) {
            throw new RuntimeException('Falha ao criar usuario de teste: ' . json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        return $user;
    }

    private function createElection(): Election
    {
        $election = new Election([
            'title' => 'Eleicao Smoke',
            'description' => 'Eleicao criada durante teste funcional',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
        ]);

        if (!$election->save()) {
            throw new RuntimeException('Falha ao criar eleicao de teste: ' . json_encode($election->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        return $election;
    }

    private function createCandidate(User $user, Election $election, string $displayName): Candidate
    {
        $candidate = new Candidate([
            'user_id' => $user->id,
            'election_id' => $election->id,
            'display_name' => $displayName,
            'bio' => 'Bio de teste',
        ]);

        if (!$candidate->save()) {
            throw new RuntimeException('Falha ao criar candidato de teste: ' . json_encode($candidate->getErrors(), JSON_UNESCAPED_UNICODE));
        }

        return $candidate;
    }
}
