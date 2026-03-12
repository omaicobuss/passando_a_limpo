<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class CandidateSearch extends Candidate
{
    public function rules(): array
    {
        return [
            [['election_id'], 'integer'],
            [['display_name'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Candidate::find()
            ->with(['user', 'election'])
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['election_id' => $this->election_id]);
        $query->andFilterWhere(['like', 'display_name', $this->display_name]);

        return $dataProvider;
    }
}
