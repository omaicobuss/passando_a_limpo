<?php

namespace app\controllers;

use app\models\Candidate;
use app\models\Election;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class CandidateController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['index', 'view'], 'allow' => true],
                    ['actions' => ['create', 'update', 'delete'], 'allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Candidate::find()->with(['user', 'election'])->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'proposals' => $model->getProposals()->orderBy(['created_at' => SORT_DESC])->all(),
        ]);
    }

    public function actionCreate()
    {
        $model = new Candidate();
        if (!Yii::$app->user->identity?->isAdmin()) {
            $model->user_id = Yii::$app->user->id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $this->assertCanManage($model);
            if ($model->save()) {
                $user = User::findOne($model->user_id);
                if ($user !== null && $user->role === 'citizen') {
                    $user->role = 'candidate';
                    $user->save(false, ['role']);
                }
                Yii::$app->session->setFlash('success', 'Candidato salvo.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'userOptions' => $this->userOptions(),
            'electionOptions' => $this->electionOptions(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $this->assertCanManage($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Candidato atualizado.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'userOptions' => $this->userOptions(),
            'electionOptions' => $this->electionOptions(),
        ]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        $this->assertCanManage($model);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Candidato removido.');
        return $this->redirect(['index']);
    }

    protected function assertCanManage(Candidate $candidate): void
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            throw new ForbiddenHttpException('Acesso negado.');
        }
        if (!$user->isAdmin() && (int) $candidate->user_id !== (int) $user->id) {
            throw new ForbiddenHttpException('Você não pode alterar este candidato.');
        }
    }

    protected function findModel(int $id): Candidate
    {
        $model = Candidate::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Candidato não encontrado.');
        }
        return $model;
    }

    protected function userOptions(): array
    {
        if (!Yii::$app->user->identity?->isAdmin()) {
            $id = Yii::$app->user->id;
            $user = User::findOne($id);
            return $user ? [$user->id => $user->username] : [];
        }
        return ArrayHelper::map(User::find()->orderBy(['username' => SORT_ASC])->all(), 'id', 'username');
    }

    protected function electionOptions(): array
    {
        return ArrayHelper::map(Election::find()->orderBy(['start_date' => SORT_DESC])->all(), 'id', 'title');
    }
}
