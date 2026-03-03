<?php

namespace app\models;

use yii\base\Model;

class DeleteAccountForm extends Model
{
    public const CONFIRMATION_TEXT = 'EXCLUIR MINHA CONTA';

    public string $password = '';
    public string $confirmation = '';

    private User $user;

    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['password', 'confirmation'], 'required'],
            [['password'], 'validatePassword'],
            [['confirmation'], 'compare', 'compareValue' => self::CONFIRMATION_TEXT],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'password' => 'Confirme sua senha',
            'confirmation' => 'Digite EXCLUIR MINHA CONTA',
        ];
    }

    public function validatePassword(string $attribute): void
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!$this->user->validatePassword($this->password)) {
            $this->addError($attribute, 'Senha invalida.');
        }
    }

    public function deleteAccount(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        return (bool) $this->user->delete();
    }
}
