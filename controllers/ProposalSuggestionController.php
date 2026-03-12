<?php

namespace app\controllers;

use app\models\ProposalSuggestion;
use app\models\ProposalSuggestionVote;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProposalSuggestionController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['view'], 'allow' => true],
                    ['actions' => ['create'], 'allow' => true, 'roles' => ['commentProposal']],
                    ['actions' => ['vote'], 'allow' => true, 'roles' => ['voteProposal']],
                    ['actions' => ['moderate'], 'allow' => true, 'roles' => ['moderateSuggestion']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'vote' => ['POST'],
                    'moderate' => ['POST'],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new ProposalSuggestion();
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Sugestão enviada.');
            } else {
                Yii::$app->session->setFlash('error', 'Não foi possível enviar a sugestão.');
            }
            return $this->redirect(['proposal/view', 'id' => $model->proposal_id]);
        }

        throw new NotFoundHttpException('Requisição inválida.');
    }

    public function actionView(int $id): string
    {
        $model = ProposalSuggestion::find()->with(['proposal', 'user', 'votes'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Sugestão não encontrada.');
        }

        return $this->render('view', ['model' => $model]);
    }

    public function actionVote(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $suggestionId = (int) Yii::$app->request->post('suggestion_id');
        $value = (int) Yii::$app->request->post('value');
        if (!in_array($value, [-1, 1], true)) {
            return ['success' => false, 'message' => 'Voto inválido'];
        }

        $suggestion = ProposalSuggestion::findOne($suggestionId);
        if ($suggestion === null) {
            return ['success' => false, 'message' => 'Sugestão não encontrada'];
        }

        $vote = ProposalSuggestionVote::findOne(['suggestion_id' => $suggestionId, 'user_id' => Yii::$app->user->id]);
        if ($vote === null) {
            $vote = new ProposalSuggestionVote([
                'suggestion_id' => $suggestionId,
                'user_id' => Yii::$app->user->id,
                'value' => $value,
            ]);
        } else {
            $vote->value = $value;
        }

        if (!$vote->save()) {
            return ['success' => false, 'errors' => $vote->getErrors()];
        }

        return ['success' => true, 'score' => $suggestion->getScore()];
    }

    public function actionModerate(int $id, string $status)
    {
        $model = ProposalSuggestion::find()->with(['proposal.candidate'])->where(['id' => $id])->one();
        if ($model === null) {
            throw new NotFoundHttpException('Sugestão não encontrada.');
        }

        if (!in_array($status, [ProposalSuggestion::STATUS_APPROVED, ProposalSuggestion::STATUS_REJECTED], true)) {
            throw new NotFoundHttpException('Status inválido.');
        }

        if (!Yii::$app->user->can('moderateSuggestion', ['suggestion' => $model])) {
            throw new ForbiddenHttpException('Você não pode moderar essa sugestão.');
        }

        $model->status = $status;
        $model->moderated_by = (int) Yii::$app->user->id;
        $model->moderated_at = time();
        $model->save(false, ['status', 'moderated_by', 'moderated_at', 'updated_at']);

        Yii::$app->session->setFlash('success', 'Sugestão moderada.');
        return $this->redirect(['proposal/view', 'id' => $model->proposal_id]);
    }
}
