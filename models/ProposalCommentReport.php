<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalCommentReport extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%proposal_comment_report}}';
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
            [['comment_id', 'user_id'], 'required'],
            [['comment_id', 'user_id', 'created_at'], 'integer'],
            [['reason'], 'string', 'max' => 255],
            [['comment_id'], 'exist', 'targetClass' => ProposalComment::class, 'targetAttribute' => ['comment_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['comment_id', 'user_id'], 'unique', 'targetAttribute' => ['comment_id', 'user_id']],
        ];
    }

    public function getComment()
    {
        return $this->hasOne(ProposalComment::class, ['id' => 'comment_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}