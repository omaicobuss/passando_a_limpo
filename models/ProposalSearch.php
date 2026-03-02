<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProposalSearch extends Proposal
{
    public string $sort = 'popular';

    public function rules(): array
    {
        return [
            [['election_id', 'candidate_id'], 'integer'],
            [['theme', 'fulfillment_status', 'title', 'sort'], 'safe'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Proposal::find()->joinWith(['candidate', 'election']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 12],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['proposal.election_id' => $this->election_id]);
        $query->andFilterWhere(['proposal.candidate_id' => $this->candidate_id]);
        $query->andFilterWhere(['proposal.fulfillment_status' => $this->fulfillment_status]);
        $query->andFilterWhere(['like', 'proposal.theme', $this->theme]);
        $query->andFilterWhere(['like', 'proposal.title', $this->title]);

        switch ($this->sort) {
            case 'newest':
                $query->orderBy(['proposal.created_at' => SORT_DESC]);
                break;
            case 'oldest':
                $query->orderBy(['proposal.created_at' => SORT_ASC]);
                break;
            default:
                $query->orderBy(['proposal.score' => SORT_DESC, 'proposal.created_at' => SORT_DESC]);
                break;
        }

        return $dataProvider;
    }

    public static function sortOptions(): array
    {
        return [
            'popular' => 'Mais populares',
            'newest' => 'Mais recentes',
            'oldest' => 'Mais antigas',
        ];
    }
}
