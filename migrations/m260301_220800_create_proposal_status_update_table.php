<?php

use yii\db\Migration;

class m260301_220800_create_proposal_status_update_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_status_update}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_status_update}}', [
            'id' => $this->primaryKey(),
            'proposal_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(32)->notNull(),
            'description' => $this->text()->notNull(),
            'update_date' => $this->date()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_status_update_proposal_id', '{{%proposal_status_update}}', 'proposal_id');
        $this->createIndex('idx_status_update_user_id', '{{%proposal_status_update}}', 'user_id');
        $this->createIndex('idx_status_update_status', '{{%proposal_status_update}}', 'status');

        $this->addForeignKey(
            'fk_status_update_proposal_id',
            '{{%proposal_status_update}}',
            'proposal_id',
            '{{%proposal}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_status_update_user_id',
            '{{%proposal_status_update}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_status_update}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_status_update_proposal_id', '{{%proposal_status_update}}');
        $this->dropForeignKey('fk_status_update_user_id', '{{%proposal_status_update}}');
        $this->dropTable('{{%proposal_status_update}}');
    }
}
