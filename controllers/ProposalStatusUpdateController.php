<?php

namespace app\controllers;

use app\models\Proposal;
use app\models\ProposalStatusUpdate;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ProposalStatusUpdateController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['create'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST']],
            ],
        ];
    }

    public function actionCreate()
    {
        $model = new ProposalStatusUpdate();
        $model->user_id = Yii::$app->user->id;
        if (!$model->load(Yii::$app->request->post(), '')) {
            throw new NotFoundHttpException('Requisição inválida.');
        }

        $proposal = Proposal::find()->with('candidate')->where(['id' => $model->proposal_id])->one();
        if ($proposal === null) {
            throw new NotFoundHttpException('Proposta não encontrada.');
        }

        $user = Yii::$app->user->identity;
        if (!$user->isAdmin() && (int) $proposal->candidate->user_id !== (int) $user->id) {
            throw new ForbiddenHttpException('Você não pode atualizar essa proposta.');
        }

        if ($model->save()) {
            $proposal->fulfillment_status = $model->status;
            $proposal->save(false, ['fulfillment_status', 'updated_at']);
            Yii::$app->session->setFlash('success', 'Atualização registrada.');
        } else {
            Yii::$app->session->setFlash('error', 'Não foi possível salvar a atualização.');
        }

        return $this->redirect(['proposal/view', 'id' => $model->proposal_id]);
    }
}
