<?php

namespace app\controllers;

use app\models\Candidate;
use app\models\Election;
use app\models\CandidateSearch;
use app\models\User;
use Yii;
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
                    ['actions' => ['create', 'update', 'delete'], 'allow' => true, 'roles' => ['candidate']],
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
        $searchModel = new CandidateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'electionOptions' => $this->electionOptions(),
        ]);
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
        $this->enforceCurrentUserOwnership($model);

        if ($model->load(Yii::$app->request->post())) {
            $this->enforceCurrentUserOwnership($model);
            $this->assertCanManage($model);
            if ($model->save()) {
                $user = User::findOne($model->user_id);
                if ($user !== null && !$user->hasRole('candidate')) {
                    $user->role = 'candidate';
                    $user->save(false, ['role', 'updated_at']);
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

        if ($model->load(Yii::$app->request->post())) {
            $this->enforceCurrentUserOwnership($model);
            $this->assertCanManage($model);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Candidato atualizado.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
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
        $userId = (int) Yii::$app->user->id;

        if (!Yii::$app->user->can('admin') && (int) $candidate->user_id !== $userId) {
            throw new ForbiddenHttpException('Você não pode alterar este candidato.');
        }

        if (!Yii::$app->user->can('manageCandidate', ['candidate' => $candidate])) {
            throw new ForbiddenHttpException('Você não pode alterar este candidato.');
        }
    }

    protected function enforceCurrentUserOwnership(Candidate $candidate): void
    {
        if (!Yii::$app->user->can('admin')) {
            $candidate->user_id = (int) Yii::$app->user->id;
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
        if (!Yii::$app->user->can('admin')) {
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
