<?php

namespace app\controllers;

use app\models\Candidate;
use app\models\Proposal;
use app\models\ProposalComment;
use app\models\ProposalSuggestion;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class CandidatePanelController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['candidate'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        if (!Yii::$app->user->can('candidate')) {
            throw new ForbiddenHttpException('Apenas candidatos podem acessar este painel.');
        }

        $user = Yii::$app->user->identity;

        $candidateIds = Candidate::find()->select('id');
        if (!Yii::$app->user->can('admin')) {
            $candidateIds->andWhere(['user_id' => $user->id]);
        }
        $candidateIdList = $candidateIds->column();

        $proposalQuery = Proposal::find()->where(['candidate_id' => $candidateIdList]);
        $proposalIds = $proposalQuery->select('id')->column();

        $stats = [
            'totalProposals' => $proposalQuery->count(),
            'avgScore' => (float) (Proposal::find()->where(['id' => $proposalIds])->average('score') ?: 0),
            'recentComments' => ProposalComment::find()->where(['proposal_id' => $proposalIds])->orderBy(['created_at' => SORT_DESC])->limit(5)->all(),
            'pendingSuggestions' => ProposalSuggestion::find()->where(['proposal_id' => $proposalIds, 'status' => ProposalSuggestion::STATUS_PENDING])->count(),
            'myProposals' => Proposal::find()->where(['id' => $proposalIds])->orderBy(['created_at' => SORT_DESC])->limit(10)->all(),
        ];

        return $this->render('index', ['stats' => $stats]);
    }
}
