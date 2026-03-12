<?php

namespace app\models;

use yii\base\Model;

class SignupForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            [['username', 'email', 'password'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'string', 'min' => 3, 'max' => 255],
            [['email'], 'email'],
            [['password'], 'string', 'min' => 6],
            [['username'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],
            [['email'], 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Usuário',
            'email' => 'E-mail',
            'password' => 'Senha',
        ];
    }

    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->role = 'citizen';
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
