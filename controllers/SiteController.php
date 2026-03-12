<?php

namespace app\controllers;

use Yii;
use app\models\AccountProfileForm;
use app\models\Candidate;
use app\models\CandidateUpgradeRequest;
use app\models\CandidateUpgradeRequestForm;
use app\models\ChangePasswordForm;
use app\models\ContactForm;
use app\models\DeleteAccountForm;
use app\models\LoginForm;
use app\models\Proposal;
use app\models\ProposalComment;
use app\models\ProposalStatusUpdate;
use app\models\ProposalSuggestion;
use app\models\ProposalSuggestionVote;
use app\models\ProposalVote;
use app\models\SignupForm;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => [
                    'logout',
                    'signup',
                    'my-account',
                    'account-update-profile',
                    'account-change-password',
                    'account-export-data',
                    'account-delete',
                    'account-request-candidate',
                    'candidate-requests',
                    'candidate-request-review',
                    'candidate-request-document',
                    'my-candidates',
                    'my-proposals',
                    'my-comments',
                    'my-suggestions',
                    'my-proposal-votes',
                    'my-suggestion-votes',
                    'my-status-updates',
                ],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'my-account',
                            'account-update-profile',
                            'account-change-password',
                            'account-export-data',
                            'account-delete',
                            'account-request-candidate',
                            'candidate-request-document',
                            'my-candidates',
                            'my-proposals',
                            'my-comments',
                            'my-suggestions',
                            'my-proposal-votes',
                            'my-suggestion-votes',
                            'my-status-updates',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => [
                            'candidate-requests',
                            'candidate-request-review',
                        ],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'account-update-profile' => ['post'],
                    'account-change-password' => ['post'],
                    'account-export-data' => ['post'],
                    'account-delete' => ['post'],
                    'account-request-candidate' => ['post'],
                    'candidate-request-review' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'latestProposals' => \app\models\Proposal::find()->orderBy(['created_at' => SORT_DESC])->limit(6)->all(),
            'activeElections' => \app\models\Election::find()->orderBy(['start_date' => SORT_DESC])->limit(4)->all(),
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->signup();
            if ($user !== null) {
                Yii::$app->session->setFlash('success', 'Conta criada com sucesso. Faça login para continuar.');
                return $this->redirect(['site/login']);
            }
        }

        return $this->render('signup', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionMyAccount(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        $profileForm = new AccountProfileForm($user);
        $passwordForm = new ChangePasswordForm($user);
        $deleteAccountForm = new DeleteAccountForm($user);
        $candidateUpgradeRequestForm = new CandidateUpgradeRequestForm();
        $latestCandidateUpgradeRequest = CandidateUpgradeRequest::find()
            ->with(['reviewer'])
            ->where(['user_id' => $user->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();

        $activitySummary = [
            ['label' => 'Perfis de candidato', 'value' => Candidate::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-candidates']],
            ['label' => 'Propostas publicadas', 'value' => Proposal::find()
                ->alias('p')
                ->innerJoin(['c' => Candidate::tableName()], 'c.id = p.candidate_id')
                ->where(['c.user_id' => $user->id])
                ->count(), 'url' => ['site/my-proposals']],
            ['label' => 'Comentarios', 'value' => ProposalComment::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-comments']],
            ['label' => 'Sugestoes em propostas', 'value' => ProposalSuggestion::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-suggestions']],
            ['label' => 'Votos em propostas', 'value' => ProposalVote::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-proposal-votes']],
            ['label' => 'Votos em sugestoes', 'value' => ProposalSuggestionVote::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-suggestion-votes']],
            ['label' => 'Atualizacoes de status', 'value' => ProposalStatusUpdate::find()->where(['user_id' => $user->id])->count(), 'url' => ['site/my-status-updates']],
        ];

        return $this->render('my-account', [
            'user' => $user,
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
            'deleteAccountForm' => $deleteAccountForm,
            'candidateUpgradeRequestForm' => $candidateUpgradeRequestForm,
            'latestCandidateUpgradeRequest' => $latestCandidateUpgradeRequest,
            'activitySummary' => $activitySummary,
        ]);
    }

    public function actionAccountUpdateProfile(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $model = new AccountProfileForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Dados da conta atualizados com sucesso.');
        } else {
            $errors = $model->getFirstErrors();
            $message = !empty($errors) ? reset($errors) : 'Nao foi possivel atualizar os dados da conta.';
            Yii::$app->session->setFlash('error', $message);
        }

        return $this->redirect(['site/my-account', '#' => 'dados']);
    }

    public function actionAccountChangePassword(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $model = new ChangePasswordForm($user);

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Senha atualizada com sucesso.');
        } else {
            $errors = $model->getFirstErrors();
            $message = !empty($errors) ? reset($errors) : 'Nao foi possivel atualizar a senha.';
            Yii::$app->session->setFlash('error', $message);
        }

        return $this->redirect(['site/my-account', '#' => 'seguranca']);
    }

    public function actionAccountExportData(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $content = json_encode($this->buildAccountExportData($user), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = sprintf('meus-dados-%s.json', date('Ymd-His'));

        return Yii::$app->response->sendContentAsFile(
            $content === false ? '{}' : $content,
            $filename,
            ['mimeType' => 'application/json']
        );
    }

    public function actionAccountDelete(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $model = new DeleteAccountForm($user);

        if ($this->isLastActiveAdmin($user)) {
            Yii::$app->session->setFlash('error', 'Nao e permitido excluir o ultimo administrador ativo.');
            return $this->redirect(['site/my-account', '#' => 'lgpd']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->deleteAccount()) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('success', 'Conta excluida com sucesso.');
            return $this->goHome();
        }

        $errors = $model->getFirstErrors();
        $message = !empty($errors) ? reset($errors) : 'Nao foi possivel excluir a conta. Revise os dados de confirmacao.';
        Yii::$app->session->setFlash('error', $message);
        return $this->redirect(['site/my-account', '#' => 'lgpd']);
    }

    public function actionAccountRequestCandidate(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (Yii::$app->user->can('candidate')) {
            Yii::$app->session->setFlash('error', 'A solicitacao de candidatura e exclusiva para usuarios citizen.');
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        $hasPending = CandidateUpgradeRequest::find()
            ->where(['user_id' => $user->id, 'status' => CandidateUpgradeRequest::STATUS_PENDING])
            ->exists();
        if ($hasPending) {
            Yii::$app->session->setFlash('error', 'Ja existe uma solicitacao pendente de analise.');
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        $form = new CandidateUpgradeRequestForm();
        $formName = $form->formName();
        $formPost = Yii::$app->request->post($formName, []);
        if (is_array($formPost)) {
            // File input is handled exclusively by UploadedFile::getInstance().
            unset($formPost['document']);
            $form->load([$formName => $formPost]);
        }
        $form->document = UploadedFile::getInstance($form, 'document');

        if (!$form->validate()) {
            $errors = $form->getFirstErrors();
            $message = !empty($errors) ? reset($errors) : 'Nao foi possivel enviar a solicitacao.';
            Yii::$app->session->setFlash('error', $message);
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        $storageDir = Yii::getAlias('@runtime/uploads/candidate-upgrade-requests');
        if (!is_dir($storageDir) && !mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel preparar o armazenamento do comprovante.');
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        $extension = strtolower((string) $form->document->getExtension());
        $filename = sprintf(
            'candidate-request-%d-%d-%s.%s',
            (int) $user->id,
            time(),
            Yii::$app->security->generateRandomString(8),
            $extension
        );
        $filePath = $storageDir . DIRECTORY_SEPARATOR . $filename;
        $saved = $form->document->saveAs($filePath);
        if (!$saved) {
            // Functional tests can use synthetic uploads that are not handled by move_uploaded_file().
            $saved = $form->document->saveAs($filePath, false);
        }
        if (!$saved) {
            Yii::$app->session->setFlash('error', 'Falha ao salvar o comprovante enviado.');
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        $request = new CandidateUpgradeRequest([
            'user_id' => $user->id,
            'document_path' => $filename,
            'message' => $form->message,
            'status' => CandidateUpgradeRequest::STATUS_PENDING,
        ]);

        if (!$request->save()) {
            @unlink($filePath);
            $errors = $request->getFirstErrors();
            $message = !empty($errors) ? reset($errors) : 'Nao foi possivel registrar sua solicitacao.';
            Yii::$app->session->setFlash('error', $message);
            return $this->redirect(['site/my-account', '#' => 'candidatura']);
        }

        Yii::$app->session->setFlash('success', 'Solicitacao enviada com sucesso. Aguarde a analise de um administrador.');
        return $this->redirect(['site/my-account', '#' => 'candidatura']);
    }

    public function actionCandidateRequests(): string
    {
        $this->assertAdmin();

        $dataProvider = new ActiveDataProvider([
            'query' => CandidateUpgradeRequest::find()
                ->with(['user', 'reviewer'])
                ->orderBy(new Expression("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END, created_at DESC")),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('candidate-requests', ['dataProvider' => $dataProvider]);
    }

    public function actionCandidateRequestDocument(int $id): Response
    {
        $request = CandidateUpgradeRequest::findOne($id);
        if ($request === null) {
            throw new NotFoundHttpException('Solicitacao nao encontrada.');
        }

        if (!Yii::$app->user->can('viewCandidateRequestDocument', ['candidateRequest' => $request])) {
            throw new ForbiddenHttpException('Voce nao pode acessar este comprovante.');
        }

        $filename = basename((string) $request->document_path);
        $filePath = Yii::getAlias('@runtime/uploads/candidate-upgrade-requests/' . $filename);
        if (!is_file($filePath)) {
            throw new NotFoundHttpException('Arquivo de comprovante nao encontrado.');
        }

        return Yii::$app->response->sendFile($filePath, $filename);
    }

    public function actionCandidateRequestReview(int $id, string $decision): Response
    {
        $this->assertAdmin();

        if (!in_array($decision, ['approve', 'reject'], true)) {
            throw new NotFoundHttpException('Decisao invalida.');
        }

        $request = CandidateUpgradeRequest::findOne($id);
        if ($request === null) {
            throw new NotFoundHttpException('Solicitacao nao encontrada.');
        }

        if ($request->status !== CandidateUpgradeRequest::STATUS_PENDING) {
            Yii::$app->session->setFlash('error', 'Esta solicitacao ja foi analisada.');
            return $this->redirect(['site/candidate-requests']);
        }

        $request->status = $decision === 'approve'
            ? CandidateUpgradeRequest::STATUS_APPROVED
            : CandidateUpgradeRequest::STATUS_REJECTED;
        $request->admin_notes = (string) Yii::$app->request->post('admin_notes', '');
        $request->reviewed_by = (int) Yii::$app->user->id;
        $request->reviewed_at = time();

        if ($request->save(false, ['status', 'admin_notes', 'reviewed_by', 'reviewed_at', 'updated_at'])) {
            if ($decision === 'approve') {
                $user = $request->user;
                if ($user !== null && !$user->hasRole('candidate')) {
                    $user->role = 'candidate';
                    $user->save(false, ['role', 'updated_at']);
                }
                $this->sendCandidateRequestDecisionNotification($request, true);
                Yii::$app->session->setFlash('success', 'Solicitacao aprovada e perfil atualizado para candidate.');
            } else {
                $this->sendCandidateRequestDecisionNotification($request, false);
                Yii::$app->session->setFlash('success', 'Solicitacao reprovada.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Nao foi possivel concluir a analise da solicitacao.');
        }

        return $this->redirect(['site/candidate-requests']);
    }

    private function isLastActiveAdmin(User $user): bool
    {
        if (!Yii::$app->authManager?->checkAccess((int) $user->id, 'admin')) {
            return false;
        }

        $adminIds = Yii::$app->authManager?->getUserIdsByRole('admin') ?? [];
        if ($adminIds === []) {
            return false;
        }

        $activeAdmins = User::find()
            ->where(['id' => $adminIds, 'status' => User::STATUS_ACTIVE])
            ->count();

        return (int) $activeAdmins <= 1;
    }

    private function buildAccountExportData(User $user): array
    {
        return [
            'exported_at' => date(DATE_ATOM),
            'user' => [
                'id' => (int) $user->id,
                'username' => (string) $user->username,
                'email' => (string) $user->email,
                'role' => (string) $user->role,
                'status' => (int) $user->status,
                'created_at' => (int) $user->created_at,
                'updated_at' => (int) $user->updated_at,
            ],
            'candidates' => Candidate::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposals' => Proposal::find()
                ->alias('p')
                ->select('p.*')
                ->innerJoin(['c' => Candidate::tableName()], 'c.id = p.candidate_id')
                ->where(['c.user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposal_comments' => ProposalComment::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposal_suggestions' => ProposalSuggestion::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposal_votes' => ProposalVote::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposal_suggestion_votes' => ProposalSuggestionVote::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'proposal_status_updates' => ProposalStatusUpdate::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
            'candidate_upgrade_requests' => CandidateUpgradeRequest::find()
                ->where(['user_id' => $user->id])
                ->asArray()
                ->all(),
        ];
    }

    public function actionMyCandidates(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => Candidate::find()
                ->with(['election'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-candidates', ['dataProvider' => $dataProvider]);
    }

    public function actionMyProposals(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => Proposal::find()
                ->alias('p')
                ->with(['election', 'candidate'])
                ->innerJoin(['c' => Candidate::tableName()], 'c.id = p.candidate_id')
                ->where(['c.user_id' => $user->id])
                ->orderBy(['p.created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-proposals', ['dataProvider' => $dataProvider]);
    }

    public function actionMyComments(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => ProposalComment::find()
                ->with(['proposal'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-comments', ['dataProvider' => $dataProvider]);
    }

    public function actionMySuggestions(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => ProposalSuggestion::find()
                ->with(['proposal'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-suggestions', ['dataProvider' => $dataProvider]);
    }

    public function actionMyProposalVotes(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => ProposalVote::find()
                ->with(['proposal'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-proposal-votes', ['dataProvider' => $dataProvider]);
    }

    public function actionMySuggestionVotes(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => ProposalSuggestionVote::find()
                ->with(['suggestion', 'suggestion.proposal'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-suggestion-votes', ['dataProvider' => $dataProvider]);
    }

    public function actionMyStatusUpdates(): string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $dataProvider = new ActiveDataProvider([
            'query' => ProposalStatusUpdate::find()
                ->with(['proposal'])
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('my-status-updates', ['dataProvider' => $dataProvider]);
    }

    private function assertAdmin(): void
    {
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Apenas administradores podem acessar este recurso.');
        }
    }

    private function sendCandidateRequestDecisionNotification(CandidateUpgradeRequest $request, bool $approved): void
    {
        $user = $request->user;
        $email = trim((string) ($user->email ?? ''));
        if ($email == '') {
            return;
        }

        $subjectStatus = $approved ? 'Aprovada' : 'Reprovada';
        $subject = sprintf('Solicitacao de candidatura %s', $subjectStatus);

        try {
            Yii::$app->mailer->compose(
                ['html' => 'candidate-upgrade-decision-html', 'text' => 'candidate-upgrade-decision-text'],
                [
                    'request' => $request,
                    'user' => $user,
                    'approved' => $approved,
                ]
            )
                ->setTo($email)
                ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
                ->setSubject($subject)
                ->send();
        } catch (\Throwable $e) {
            Yii::warning(
                sprintf(
                    'Falha ao enviar notificacao de solicitacao de candidatura. request_id=%d user_id=%d error=%s',
                    (int) $request->id,
                    (int) $request->user_id,
                    $e->getMessage()
                ),
                __METHOD__
            );
        }
    }
}
