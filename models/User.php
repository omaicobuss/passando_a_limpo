<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_ACTIVE = 10;

    public static function tableName(): string
    {
        return '{{%user}}';
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
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'string', 'min' => 3, 'max' => 255],
            [['email'], 'email'],
            [['role'], 'in', 'range' => ['admin', 'candidate', 'citizen']],
            [['status'], 'integer'],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_hash', 'auth_key'], 'string', 'max' => 255],
            [['role'], 'default', 'value' => 'citizen'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Usuário',
            'email' => 'E-mail',
            'role' => 'Papel',
            'status' => 'Status',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken(string $token): ?self
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne(['password_reset_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function isPasswordResetTokenValid(string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = 3600;
        return $timestamp + $expire >= time();
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getAuthKey(): string
    {
        return (string) $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken(): void
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken(): void
    {
        $this->password_reset_token = null;
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert || array_key_exists('role', $changedAttributes)) {
            $role = (string) ($this->role ?: 'citizen');
            $this->assignRbacRole($role);
        }
    }

    public function afterDelete(): void
    {
        parent::afterDelete();

        $auth = Yii::$app->authManager;
        if ($auth !== null) {
            $auth->revokeAll((int) $this->id);
        }
    }

    public function hasRole(string $role): bool
    {
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            return false;
        }

        return $auth->checkAccess((int) $this->id, $role);
    }

    public function assignRbacRole(string $role): bool
    {
        $auth = Yii::$app->authManager;
        if ($auth === null || (int) $this->id === 0) {
            return false;
        }

        $roleItem = $auth->getRole($role);
        if ($roleItem === null) {
            return false;
        }

        $auth->revokeAll((int) $this->id);
        $auth->assign($roleItem, (int) $this->id);
        return true;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCandidate(): bool
    {
        return $this->hasRole('candidate');
    }

    public function getCandidates()
    {
        return $this->hasMany(Candidate::class, ['user_id' => 'id']);
    }
}
