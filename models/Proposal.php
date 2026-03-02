<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Proposal extends ActiveRecord
{
    public const FULFILLMENT_NOT_STARTED = 'not_started';
    public const FULFILLMENT_IN_PROGRESS = 'in_progress';
    public const FULFILLMENT_COMPLETED = 'completed';
    public const FULFILLMENT_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%proposal}}';
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
            [['election_id', 'candidate_id', 'title', 'content'], 'required'],
            [['election_id', 'candidate_id', 'score'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['theme'], 'string', 'max' => 120],
            [['fulfillment_status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['fulfillment_status'], 'default', 'value' => self::FULFILLMENT_NOT_STARTED],
            [['election_id'], 'exist', 'targetClass' => Election::class, 'targetAttribute' => ['election_id' => 'id']],
            [['candidate_id'], 'exist', 'targetClass' => Candidate::class, 'targetAttribute' => ['candidate_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'Título',
            'theme' => 'Tema',
            'content' => 'Proposta',
            'election_id' => 'Eleição',
            'candidate_id' => 'Candidato',
            'score' => 'Pontuação',
            'fulfillment_status' => 'Status de cumprimento',
        ];
    }

    public function getElection()
    {
        return $this->hasOne(Election::class, ['id' => 'election_id']);
    }

    public function getCandidate()
    {
        return $this->hasOne(Candidate::class, ['id' => 'candidate_id']);
    }

    public function getVotes()
    {
        return $this->hasMany(ProposalVote::class, ['proposal_id' => 'id']);
    }

    public function getComments()
    {
        return $this->hasMany(ProposalComment::class, ['proposal_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getSuggestions()
    {
        return $this->hasMany(ProposalSuggestion::class, ['proposal_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function getStatusUpdates()
    {
        return $this->hasMany(ProposalStatusUpdate::class, ['proposal_id' => 'id'])->orderBy(['update_date' => SORT_DESC, 'created_at' => SORT_DESC]);
    }

    public function recalculateScore(): bool
    {
        $this->score = (int) ProposalVote::find()->where(['proposal_id' => $this->id])->sum(ProposalVote::valueColumn());
        return $this->save(false, ['score', 'updated_at']);
    }

    public static function statusOptions(): array
    {
        return [
            self::FULFILLMENT_NOT_STARTED => 'Não iniciado',
            self::FULFILLMENT_IN_PROGRESS => 'Em andamento',
            self::FULFILLMENT_COMPLETED => 'Concluído',
            self::FULFILLMENT_CANCELLED => 'Cancelado',
        ];
    }

    public function beforeValidate(): bool
    {
        if ($this->hasAttribute('user_id') && empty($this->getAttribute('user_id'))) {
            $candidate = $this->candidate_id ? Candidate::findOne($this->candidate_id) : null;
            if ($candidate !== null && !empty($candidate->user_id)) {
                $this->setAttribute('user_id', (int) $candidate->user_id);
            } elseif (\Yii::$app->user !== null && !\Yii::$app->user->isGuest) {
                $this->setAttribute('user_id', (int) \Yii::$app->user->id);
            }
        }
        return parent::beforeValidate();
    }
}
