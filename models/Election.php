<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Election extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%election}}';
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
            [['title'], 'required'],
            [['description'], 'string'],
            [['start_date', 'end_date'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Título',
            'description' => 'Descrição',
            'start_date' => 'Data de início',
            'end_date' => 'Data de fim',
        ];
    }

    public function getCandidates()
    {
        return $this->hasMany(Candidate::class, ['election_id' => 'id']);
    }

    public function getProposals()
    {
        return $this->hasMany(Proposal::class, ['election_id' => 'id']);
    }

    public function beforeValidate(): bool
    {
        if ($this->hasAttribute('name') && empty($this->getAttribute('name'))) {
            $this->setAttribute('name', (string) $this->title);
        }
        if ($this->hasAttribute('year') && empty($this->getAttribute('year'))) {
            $year = $this->start_date ? (int) date('Y', strtotime((string) $this->start_date)) : (int) date('Y');
            $this->setAttribute('year', $year);
        }
        return parent::beforeValidate();
    }
}
