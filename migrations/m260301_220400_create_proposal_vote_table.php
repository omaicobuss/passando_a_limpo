<?php

use yii\db\Migration;

class m260301_220400_create_proposal_vote_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_vote}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_vote}}', [
            'id' => $this->primaryKey(),
            'proposal_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'value' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_proposal_vote_proposal_id', '{{%proposal_vote}}', 'proposal_id');
        $this->createIndex('idx_proposal_vote_user_id', '{{%proposal_vote}}', 'user_id');
        $this->createIndex('uq_proposal_vote_proposal_user', '{{%proposal_vote}}', ['proposal_id', 'user_id'], true);

        $this->addForeignKey(
            'fk_proposal_vote_proposal_id',
            '{{%proposal_vote}}',
            'proposal_id',
            '{{%proposal}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_vote_user_id',
            '{{%proposal_vote}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_vote}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_proposal_vote_proposal_id', '{{%proposal_vote}}');
        $this->dropForeignKey('fk_proposal_vote_user_id', '{{%proposal_vote}}');
        $this->dropTable('{{%proposal_vote}}');
    }
}
