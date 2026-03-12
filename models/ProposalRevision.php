<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalRevision extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proposal_revision}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['proposal_id', 'version_number', 'election_id', 'candidate_id', 'title', 'content', 'fulfillment_status'], 'required'],
            [['proposal_id', 'version_number', 'election_id', 'candidate_id', 'edited_by_user_id', 'created_at'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['theme'], 'string', 'max' => 120],
            [['fulfillment_status'], 'in', 'range' => array_keys(Proposal::statusOptions())],
            [['proposal_id'], 'exist', 'targetClass' => Proposal::class, 'targetAttribute' => ['proposal_id' => 'id']],
            [['election_id'], 'exist', 'targetClass' => Election::class, 'targetAttribute' => ['election_id' => 'id']],
            [['candidate_id'], 'exist', 'targetClass' => Candidate::class, 'targetAttribute' => ['candidate_id' => 'id']],
            [['edited_by_user_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => User::class, 'targetAttribute' => ['edited_by_user_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'version_number' => 'Versão',
            'title' => 'Título',
            'theme' => 'Tema',
            'content' => 'Proposta',
            'fulfillment_status' => 'Status de cumprimento',
            'edited_by_user_id' => 'Editado por',
            'created_at' => 'Registrado em',
        ];
    }

    public function getProposal()
    {
        return $this->hasOne(Proposal::class, ['id' => 'proposal_id']);
    }

    public function getEditor()
    {
        return $this->hasOne(User::class, ['id' => 'edited_by_user_id']);
    }
}