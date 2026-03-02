<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalStatusUpdate extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proposal_status_update}}';
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
            [['proposal_id', 'user_id', 'status', 'description', 'update_date'], 'required'],
            [['proposal_id', 'user_id'], 'integer'],
            [['description'], 'string'],
            [['update_date'], 'safe'],
            [['status'], 'in', 'range' => array_keys(Proposal::statusOptions())],
            [['proposal_id'], 'exist', 'targetClass' => Proposal::class, 'targetAttribute' => ['proposal_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getProposal()
    {
        return $this->hasOne(Proposal::class, ['id' => 'proposal_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
