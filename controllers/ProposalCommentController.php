<?php

namespace app\controllers;

use app\models\ProposalComment;
use Yii;
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
                    ['actions' => ['create', 'delete'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
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
        $model = ProposalComment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comentário não encontrado.');
        }

        $user = Yii::$app->user->identity;
        if (!$user->isAdmin() && (int) $model->user_id !== (int) $user->id) {
            throw new ForbiddenHttpException('Você não pode remover este comentário.');
        }

        $proposalId = $model->proposal_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Comentário removido.');
        return $this->redirect(['proposal/view', 'id' => $proposalId]);
    }
}
