<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ElectionSearch extends Election
{
    public function rules(): array
    {
        return [
            [['title'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Election::find()->orderBy(['start_date' => SORT_DESC, 'id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
