<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class CandidateUpgradeRequest extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function tableName(): string
    {
        return '{{%candidate_upgrade_request}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'document_path', 'status'], 'required'],
            [['user_id', 'reviewed_by', 'reviewed_at'], 'integer'],
            [['message', 'admin_notes'], 'string'],
            [['document_path'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusOptions())],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['reviewed_by'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['reviewed_by' => 'id'], 'skipOnEmpty' => true],
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_REJECTED => 'Reprovada',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? $this->status;
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getReviewer()
    {
        return $this->hasOne(User::class, ['id' => 'reviewed_by']);
    }
}
