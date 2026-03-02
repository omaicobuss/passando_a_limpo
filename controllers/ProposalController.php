<?php

namespace app\controllers;

use app\models\Candidate;
use app\models\Proposal;
use app\models\ProposalComment;
use app\models\ProposalSearch;
use app\models\ProposalStatusUpdate;
use app\models\ProposalSuggestion;
use app\models\ProposalVote;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProposalController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['index', 'view'], 'allow' => true],
                    ['actions' => ['create', 'update', 'delete', 'vote'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'vote' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $searchModel = new ProposalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'candidateOptions' => ArrayHelper::map(Candidate::find()->orderBy(['display_name' => SORT_ASC])->all(), 'id', 'display_name'),
            'electionOptions' => ArrayHelper::map(\app\models\Election::find()->orderBy(['start_date' => SORT_DESC])->all(), 'id', 'title'),
        ]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        $commentModel = new ProposalComment();
        $commentModel->proposal_id = $model->id;
        $suggestionModel = new ProposalSuggestion();
        $suggestionModel->proposal_id = $model->id;
        $statusModel = new ProposalStatusUpdate();
        $statusModel->proposal_id = $model->id;
        $statusModel->status = $model->fulfillment_status;
        $statusModel->update_date = date('Y-m-d');

        return $this->render('view', [
            'model' => $model,
            'commentModel' => $commentModel,
            'suggestionModel' => $suggestionModel,
            'statusModel' => $statusModel,
            'rootComments' => $model->getComments()->andWhere(['parent_id' => null])->all(),
        ]);
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        if (!$user?->isCandidate()) {
            throw new ForbiddenHttpException('Apenas candidatos podem criar propostas.');
        }

        $model = new Proposal();
        $candidateOptions = $this->candidateOptionsForCurrentUser();
        if (count($candidateOptions) === 1) {
            $model->candidate_id = (int) array_key_first($candidateOptions);
        }

        if ($model->load(Yii::$app->request->post())) {
            $this->assertCanEdit($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Proposta criada.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'candidateOptions' => $candidateOptions,
            'electionOptions' => $this->electionOptions(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $this->assertCanEdit($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Proposta atualizada.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'candidateOptions' => $this->candidateOptionsForCurrentUser(),
            'electionOptions' => $this->electionOptions(),
        ]);
    }

    public function actionDelete(int $id)
    {
        $user = Yii::$app->user->identity;
        if (!$user?->isAdmin()) {
            throw new ForbiddenHttpException('Apenas administradores podem excluir propostas.');
        }
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Proposta removida.');
        return $this->redirect(['index']);
    }

    public function actionVote(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $proposalId = (int) Yii::$app->request->post('proposal_id');
        $value = (int) Yii::$app->request->post('value');
        if (!in_array($value, [-1, 1], true)) {
            return ['success' => false, 'message' => 'Voto inválido.'];
        }

        $proposal = Proposal::findOne($proposalId);
        if ($proposal === null) {
            return ['success' => false, 'message' => 'Proposta não encontrada.'];
        }

        $vote = ProposalVote::findOne(['proposal_id' => $proposalId, 'user_id' => Yii::$app->user->id]);
        if ($vote === null) {
            $vote = new ProposalVote([
                'proposal_id' => $proposalId,
                'user_id' => Yii::$app->user->id,
                'value' => $value,
            ]);
        } else {
            $vote->value = $value;
        }

        if (!$vote->save()) {
            return ['success' => false, 'message' => 'Não foi possível salvar voto.', 'errors' => $vote->getErrors()];
        }

        $proposal->recalculateScore();
        return ['success' => true, 'score' => $proposal->score];
    }

    protected function candidateOptionsForCurrentUser(): array
    {
        $query = Candidate::find()->orderBy(['display_name' => SORT_ASC]);
        if (!Yii::$app->user->identity?->isAdmin()) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }
        return ArrayHelper::map($query->all(), 'id', 'display_name');
    }

    protected function electionOptions(): array
    {
        return ArrayHelper::map(\app\models\Election::find()->orderBy(['start_date' => SORT_DESC])->all(), 'id', 'title');
    }

    protected function assertCanEdit(Proposal $proposal): void
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            throw new ForbiddenHttpException('Acesso negado.');
        }
        if ($user->isAdmin()) {
            return;
        }

        if ((int) $proposal->candidate_id === 0) {
            return;
        }

        $candidate = Candidate::findOne($proposal->candidate_id);
        if ($candidate === null || (int) $candidate->user_id !== (int) $user->id) {
            throw new ForbiddenHttpException('Você não pode editar esta proposta.');
        }
    }

    protected function findModel(int $id): Proposal
    {
        $model = Proposal::find()->with(['candidate.user', 'election', 'suggestions.votes', 'statusUpdates', 'comments.user'])->where(['proposal.id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Proposta não encontrada.');
        }
        return $model;
    }
}
