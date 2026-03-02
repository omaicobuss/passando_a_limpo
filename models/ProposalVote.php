<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalVote extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proposal_vote}}';
    }

    public function behaviors(): array
    {
        $schema = static::getTableSchema();
        if ($schema->getColumn('created_at') !== null && $schema->getColumn('updated_at') !== null) {
            return [TimestampBehavior::class];
        }
        return [];
    }

    public function rules(): array
    {
        return [
            [['proposal_id', 'user_id', 'value'], 'required'],
            [['proposal_id', 'user_id', 'value'], 'integer'],
            [['value'], 'in', 'range' => [-1, 1]],
            [['proposal_id', 'user_id'], 'unique', 'targetAttribute' => ['proposal_id', 'user_id']],
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

    public static function primaryKey(): array
    {
        $schema = static::getTableSchema();
        if ($schema->getColumn('id') !== null) {
            return ['id'];
        }
        return ['proposal_id', 'user_id'];
    }

    public static function valueColumn(): string
    {
        $schema = static::getTableSchema();
        return $schema->getColumn('value') !== null ? 'value' : 'vote';
    }

    public function getValue(): int
    {
        $column = static::valueColumn();
        return (int) $this->getAttribute($column);
    }

    public function setValue($value): void
    {
        $column = static::valueColumn();
        $this->setAttribute($column, (int) $value);
    }
}
