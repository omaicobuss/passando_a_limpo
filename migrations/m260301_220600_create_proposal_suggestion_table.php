<?php

use yii\db\Migration;

class m260301_220600_create_proposal_suggestion_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_suggestion}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_suggestion}}', [
            'id' => $this->primaryKey(),
            'proposal_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'content' => $this->text()->notNull(),
            'status' => $this->string(32)->notNull()->defaultValue('pending'),
            'moderated_by' => $this->integer(),
            'moderated_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_proposal_suggestion_proposal_id', '{{%proposal_suggestion}}', 'proposal_id');
        $this->createIndex('idx_proposal_suggestion_user_id', '{{%proposal_suggestion}}', 'user_id');
        $this->createIndex('idx_proposal_suggestion_moderated_by', '{{%proposal_suggestion}}', 'moderated_by');
        $this->createIndex('idx_proposal_suggestion_status', '{{%proposal_suggestion}}', 'status');

        $this->addForeignKey(
            'fk_proposal_suggestion_proposal_id',
            '{{%proposal_suggestion}}',
            'proposal_id',
            '{{%proposal}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_suggestion_user_id',
            '{{%proposal_suggestion}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_suggestion_moderated_by',
            '{{%proposal_suggestion}}',
            'moderated_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_suggestion}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_proposal_suggestion_proposal_id', '{{%proposal_suggestion}}');
        $this->dropForeignKey('fk_proposal_suggestion_user_id', '{{%proposal_suggestion}}');
        $this->dropForeignKey('fk_proposal_suggestion_moderated_by', '{{%proposal_suggestion}}');
        $this->dropTable('{{%proposal_suggestion}}');
    }
}
