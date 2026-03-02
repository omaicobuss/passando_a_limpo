<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalSuggestionVote extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proposal_suggestion_vote}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['suggestion_id', 'user_id', 'value'], 'required'],
            [['suggestion_id', 'user_id', 'value'], 'integer'],
            [['value'], 'in', 'range' => [-1, 1]],
            [['suggestion_id', 'user_id'], 'unique', 'targetAttribute' => ['suggestion_id', 'user_id']],
            [['suggestion_id'], 'exist', 'targetClass' => ProposalSuggestion::class, 'targetAttribute' => ['suggestion_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getSuggestion()
    {
        return $this->hasOne(ProposalSuggestion::class, ['id' => 'suggestion_id']);
    }
}
