<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class ProposalComment extends ActiveRecord
{
    public const DELETED_BY_AUTHOR_MESSAGE = 'Comentário excluído pelo autor.';
    public const DELETED_BY_MODERATION_MESSAGE = 'Comentário excluído devido a denúncias de conteúdo inapropriado.';

    public static function tableName(): string
    {
        return '{{%proposal_comment}}';
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
            [['proposal_id', 'user_id', 'content'], 'required'],
            [['proposal_id', 'user_id', 'parent_id', 'deleted_at', 'deleted_by_user_id'], 'integer'],
            [['is_deleted'], 'boolean'],
            [['content'], 'string'],
            [['is_deleted'], 'default', 'value' => false],
            [['proposal_id'], 'exist', 'targetClass' => Proposal::class, 'targetAttribute' => ['proposal_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['parent_id'], 'exist', 'targetClass' => self::class, 'targetAttribute' => ['parent_id' => 'id']],
            [['deleted_by_user_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => User::class, 'targetAttribute' => ['deleted_by_user_id' => 'id']],
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

    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    public function getDeletedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'deleted_by_user_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
    }

    public function getReports()
    {
        return $this->hasMany(ProposalCommentReport::class, ['comment_id' => 'id']);
    }

    public function isDeleted(): bool
    {
        return (bool) $this->is_deleted;
    }

    public function getDisplayContent(): string
    {
        if ($this->isDeleted()) {
            if ($this->wasDeletedByAdmin()) {
                return self::DELETED_BY_MODERATION_MESSAGE;
            }

            return self::DELETED_BY_AUTHOR_MESSAGE;
        }

        return (string) $this->content;
    }

    public function wasDeletedByAdmin(): bool
    {
        $deletedByUserId = (int) $this->deleted_by_user_id;
        if ($deletedByUserId <= 0) {
            return false;
        }

        $deletedByUser = $this->deletedByUser;
        if ($deletedByUser === null) {
            $deletedByUser = User::findOne($deletedByUserId);
        }

        return $deletedByUser !== null && $deletedByUser->hasRole('admin');
    }

    public function hasUserReported(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return ProposalCommentReport::find()
            ->where(['comment_id' => $this->id, 'user_id' => $userId])
            ->exists();
    }

    public function softDelete(int $deletedByUserId): bool
    {
        if ($this->isDeleted()) {
            return true;
        }

        $this->is_deleted = true;
        $this->deleted_at = time();
        $this->deleted_by_user_id = $deletedByUserId > 0 ? $deletedByUserId : null;

        return $this->save(false, ['is_deleted', 'deleted_at', 'deleted_by_user_id', 'updated_at']);
    }
}
