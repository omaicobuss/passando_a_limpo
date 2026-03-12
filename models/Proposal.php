<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Proposal extends ActiveRecord
{
    public const REVISION_TRACKED_ATTRIBUTES = [
        'election_id',
        'candidate_id',
        'title',
        'theme',
        'content',
        'fulfillment_status',
    ];

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

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE,
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

    public function getRevisions()
    {
        return $this->hasMany(ProposalRevision::class, ['proposal_id' => 'id'])->orderBy(['version_number' => SORT_DESC]);
    }

    public function getLatestRevision()
    {
        return $this->hasOne(ProposalRevision::class, ['proposal_id' => 'id'])->orderBy(['version_number' => SORT_DESC]);
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

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if (!$this->shouldCaptureRevisionSnapshot((bool) $insert, $changedAttributes)) {
            return;
        }

        $revision = new ProposalRevision([
            'proposal_id' => (int) $this->id,
            'version_number' => $this->nextRevisionNumber(),
            'election_id' => (int) $this->election_id,
            'candidate_id' => (int) $this->candidate_id,
            'title' => (string) $this->title,
            'theme' => $this->theme,
            'content' => (string) $this->content,
            'fulfillment_status' => (string) $this->fulfillment_status,
            'edited_by_user_id' => $this->resolveEditorUserId(),
        ]);

        if (!$revision->save()) {
            throw new \RuntimeException('Falha ao registrar histórico da proposta: ' . json_encode($revision->getErrors(), JSON_UNESCAPED_UNICODE));
        }
    }

    public function hasRevisionHistory(): bool
    {
        return count($this->revisions) > 1;
    }

    public function getCurrentVersionNumber(): int
    {
        $latestRevision = $this->latestRevision;
        return $latestRevision === null ? 1 : (int) $latestRevision->version_number;
    }

    public function isEditLockedByElectionDeadline(): bool
    {
        $election = $this->election;
        if ($election === null && (int) $this->election_id > 0) {
            $election = Election::findOne((int) $this->election_id);
        }

        return $election !== null && $election->hasFinished();
    }

    protected function shouldCaptureRevisionSnapshot(bool $insert, array $changedAttributes): bool
    {
        if ($insert) {
            return true;
        }

        return count(array_intersect(array_keys($changedAttributes), self::REVISION_TRACKED_ATTRIBUTES)) > 0;
    }

    protected function nextRevisionNumber(): int
    {
        return ((int) ProposalRevision::find()->where(['proposal_id' => $this->id])->max('version_number')) + 1;
    }

    protected function resolveEditorUserId(): ?int
    {
        if (Yii::$app->has('user', true) && !Yii::$app->user->isGuest) {
            return (int) Yii::$app->user->id;
        }

        $candidate = $this->candidate;
        if ($candidate === null && $this->candidate_id) {
            $candidate = Candidate::findOne($this->candidate_id);
        }

        if ($candidate !== null && $candidate->user_id !== null) {
            return (int) $candidate->user_id;
        }

        return null;
    }
}
