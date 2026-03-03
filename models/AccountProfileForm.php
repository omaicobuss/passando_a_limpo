<?php

namespace app\models;

use yii\base\Model;

class AccountProfileForm extends Model
{
    public string $username = '';
    public string $email = '';

    private User $user;

    public function __construct(User $user, $config = [])
    {
        $this->user = $user;
        $this->username = (string) $user->username;
        $this->email = (string) $user->email;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            [['username'], 'string', 'min' => 3, 'max' => 255],
            [['email'], 'email'],
            [
                ['username'],
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'username',
                'filter' => fn ($query) => $query->andWhere(['<>', 'id', $this->user->id]),
            ],
            [
                ['email'],
                'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'filter' => fn ($query) => $query->andWhere(['<>', 'id', $this->user->id]),
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'username' => 'Usuario',
            'email' => 'E-mail',
        ];
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->user->username = $this->username;
        $this->user->email = $this->email;

        return $this->user->save(false, ['username', 'email', 'updated_at']);
    }
}
