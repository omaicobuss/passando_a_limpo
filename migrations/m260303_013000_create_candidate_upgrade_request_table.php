<?php

use yii\db\Migration;

class m260303_013000_create_candidate_upgrade_request_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%candidate_upgrade_request}}', true) !== null) {
            return;
        }

        $this->createTable('{{%candidate_upgrade_request}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'document_path' => $this->string()->notNull(),
            'message' => $this->text(),
            'status' => $this->string(32)->notNull()->defaultValue('pending'),
            'admin_notes' => $this->text(),
            'reviewed_by' => $this->integer(),
            'reviewed_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_candidate_upgrade_request_user_id', '{{%candidate_upgrade_request}}', 'user_id');
        $this->createIndex('idx_candidate_upgrade_request_status', '{{%candidate_upgrade_request}}', 'status');
        $this->createIndex('idx_candidate_upgrade_request_reviewed_by', '{{%candidate_upgrade_request}}', 'reviewed_by');

        $this->addForeignKey(
            'fk_candidate_upgrade_request_user_id',
            '{{%candidate_upgrade_request}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_candidate_upgrade_request_reviewed_by',
            '{{%candidate_upgrade_request}}',
            'reviewed_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%candidate_upgrade_request}}', true) === null) {
            return;
        }

        $this->dropForeignKey('fk_candidate_upgrade_request_user_id', '{{%candidate_upgrade_request}}');
        $this->dropForeignKey('fk_candidate_upgrade_request_reviewed_by', '{{%candidate_upgrade_request}}');
        $this->dropTable('{{%candidate_upgrade_request}}');
    }
}
