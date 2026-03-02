<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalSuggestion extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%proposal_suggestion}}';
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
            [['proposal_id', 'user_id', 'title', 'content'], 'required'],
            [['proposal_id', 'user_id', 'moderated_by', 'moderated_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['proposal_id'], 'exist', 'targetClass' => Proposal::class, 'targetAttribute' => ['proposal_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['moderated_by'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['moderated_by' => 'id'], 'skipOnEmpty' => true],
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

    public function getVotes()
    {
        return $this->hasMany(ProposalSuggestionVote::class, ['suggestion_id' => 'id']);
    }

    public function getScore(): int
    {
        return (int) $this->getVotes()->sum('value');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_REJECTED => 'Rejeitada',
        ];
    }
}
