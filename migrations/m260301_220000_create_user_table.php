<?php

use yii\db\Migration;

class m260301_220000_create_user_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) !== null) {
            return;
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'role' => $this->string(32)->notNull()->defaultValue('citizen'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $time = time();
        $security = \Yii::$app->security;
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $security->generatePasswordHash('admin123'),
            'auth_key' => $security->generateRandomString(),
            'role' => 'admin',
            'status' => 10,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%user}}', true) === null) {
            return;
        }
        $this->dropTable('{{%user}}');
    }
}
