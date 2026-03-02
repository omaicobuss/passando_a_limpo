<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Candidate extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%candidate}}';
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
            [['user_id', 'election_id', 'display_name'], 'required'],
            [['user_id', 'election_id'], 'integer'],
            [['bio'], 'string'],
            [['display_name'], 'string', 'max' => 255],
            [['user_id', 'election_id'], 'unique', 'targetAttribute' => ['user_id', 'election_id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['election_id'], 'exist', 'targetClass' => Election::class, 'targetAttribute' => ['election_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'display_name' => 'Nome de exibição',
            'bio' => 'Biografia',
            'user_id' => 'Usuário',
            'election_id' => 'Eleição',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getElection()
    {
        return $this->hasOne(Election::class, ['id' => 'election_id']);
    }

    public function getProposals()
    {
        return $this->hasMany(Proposal::class, ['candidate_id' => 'id']);
    }

    public function beforeValidate(): bool
    {
        if ($this->hasAttribute('name') && empty($this->getAttribute('name'))) {
            $this->setAttribute('name', (string) $this->display_name);
        }
        return parent::beforeValidate();
    }
}
