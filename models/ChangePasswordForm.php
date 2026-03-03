<?php

namespace app\models;

use yii\base\Model;

class ChangePasswordForm extends Model
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_repeat = '';

    private User $user;

    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['current_password', 'new_password', 'new_password_repeat'], 'required'],
            [['new_password'], 'string', 'min' => 6],
            [['new_password_repeat'], 'compare', 'compareAttribute' => 'new_password'],
            [['current_password'], 'validateCurrentPassword'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'current_password' => 'Senha atual',
            'new_password' => 'Nova senha',
            'new_password_repeat' => 'Confirmacao da nova senha',
        ];
    }

    public function validateCurrentPassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!$this->user->validatePassword($this->current_password)) {
            $this->addError($attribute, 'A senha atual informada esta incorreta.');
        }
    }

    public function changePassword(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->setPassword($this->new_password);
        $this->user->generateAuthKey();
        $this->user->removePasswordResetToken();

        if (!$this->user->save(false, ['password_hash', 'auth_key', 'password_reset_token', 'updated_at'])) {
            $this->addError('new_password', 'Nao foi possivel atualizar a senha.');
            return false;
        }

        return true;
    }
}
