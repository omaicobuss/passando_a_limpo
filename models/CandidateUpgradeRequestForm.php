<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class CandidateUpgradeRequestForm extends Model
{
    public ?UploadedFile $document = null;
    public string $message = '';

    public function rules(): array
    {
        return [
            [['document'], 'required'],
            [['message'], 'trim'],
            [['message'], 'string', 'max' => 2000],
            [['document'], 'file', 'skipOnEmpty' => false, 'extensions' => ['pdf', 'jpg', 'jpeg', 'png'], 'maxSize' => 5 * 1024 * 1024],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'document' => 'Comprovante (PDF, JPG ou PNG)',
            'message' => 'Mensagem para a analise (opcional)',
        ];
    }
}
