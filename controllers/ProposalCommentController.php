<?php

namespace app\controllers;

use app\models\ProposalComment;
use app\models\ProposalCommentReport;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ProposalCommentController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['create'], 'allow' => true, 'roles' => ['commentProposal']],
                    ['actions' => ['reported', 'resolve-report'], 'allow' => true, 'roles' => ['admin']],
                    ['actions' => ['delete', 'mark-inappropriate'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'reported' => ['GET'],
                    'create' => ['POST'],
                    'delete' => ['POST'],
                    'mark-inappropriate' => ['POST'],
                    'resolve-report' => ['POST'],
                ],
            ],
        ];
    }

    public function actionReported(): string
    {
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Apenas administradores podem moderar comentários denunciados.');
        }

        $query = ProposalComment::find()
            ->alias('c')
            ->innerJoin(['r' => ProposalCommentReport::tableName()], 'r.comment_id = c.id')
            ->with(['proposal', 'user'])
            ->select([
                'c.*',
                'report_count' => new Expression('COUNT(r.id)'),
                'last_reported_at' => new Expression('MAX(r.created_at)'),
            ])
            ->groupBy('c.id')
            ->orderBy([
                'last_reported_at' => SORT_DESC,
                'report_count' => SORT_DESC,
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('reported', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new ProposalComment();
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->request->post(), '')) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Comentário publicado.');
            } else {
                Yii::$app->session->setFlash('error', 'Falha ao publicar comentário.');
            }
            return $this->redirect(['proposal/view', 'id' => $model->proposal_id]);
        }
        throw new NotFoundHttpException('Requisição inválida.');
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('deleteOwnComment', ['comment' => $model])) {
            throw new ForbiddenHttpException('Você não pode remover este comentário.');
        }

        $proposalId = $model->proposal_id;

        if ($model->isDeleted()) {
            Yii::$app->session->setFlash('info', 'Este comentário já foi removido.');
            return $this->redirectAfterAction($proposalId);
        }

        if ($model->softDelete((int) Yii::$app->user->id)) {
            ProposalCommentReport::deleteAll(['comment_id' => (int) $model->id]);
            Yii::$app->session->setFlash('success', 'Comentário removido.');
        } else {
            Yii::$app->session->setFlash('error', 'Falha ao remover comentário.');
        }

        return $this->redirectAfterAction($proposalId);
    }

    public function actionMarkInappropriate(int $id)
    {
        $model = $this->findModel($id);
        $proposalId = (int) $model->proposal_id;
        $userId = (int) Yii::$app->user->id;

        if ($model->isDeleted()) {
            Yii::$app->session->setFlash('info', 'Não é possível marcar um comentário já removido.');
            return $this->redirect(['proposal/view', 'id' => $proposalId]);
        }

        if ((int) $model->user_id === $userId) {
            Yii::$app->session->setFlash('error', 'Você não pode marcar seu próprio comentário.');
            return $this->redirect(['proposal/view', 'id' => $proposalId]);
        }

        if ($model->hasUserReported($userId)) {
            Yii::$app->session->setFlash('info', 'Você já marcou este comentário como inapropriado.');
            return $this->redirect(['proposal/view', 'id' => $proposalId]);
        }

        $report = new ProposalCommentReport([
            'comment_id' => (int) $model->id,
            'user_id' => $userId,
            'reason' => trim((string) Yii::$app->request->post('reason', '')) ?: null,
        ]);

        if ($report->save()) {
            Yii::$app->session->setFlash('success', 'Comentário marcado como inapropriado.');
        } else {
            Yii::$app->session->setFlash('error', 'Não foi possível registrar a marcação.');
        }

        return $this->redirect(['proposal/view', 'id' => $proposalId]);
    }

    public function actionResolveReport(int $id)
    {
        if (!Yii::$app->user->can('admin')) {
            throw new ForbiddenHttpException('Apenas administradores podem moderar comentários denunciados.');
        }

        $model = $this->findModel($id);
        $removed = ProposalCommentReport::deleteAll(['comment_id' => (int) $model->id]);

        if ($removed > 0) {
            Yii::$app->session->setFlash('success', 'Denúncias arquivadas para o comentário.');
        } else {
            Yii::$app->session->setFlash('info', 'Não há denúncias pendentes para este comentário.');
        }

        return $this->redirect(['reported']);
    }

    protected function findModel(int $id): ProposalComment
    {
        $model = ProposalComment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comentário não encontrado.');
        }

        return $model;
    }

    protected function redirectAfterAction(int $proposalId)
    {
        if ((string) Yii::$app->request->post('back') === 'reported') {
            return $this->redirect(['reported']);
        }

        return $this->redirect(['proposal/view', 'id' => $proposalId]);
    }
}
