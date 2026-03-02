<?php

use yii\db\Migration;

class m260301_220700_create_proposal_suggestion_vote_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_suggestion_vote}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_suggestion_vote}}', [
            'id' => $this->primaryKey(),
            'suggestion_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'value' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_suggestion_vote_suggestion_id', '{{%proposal_suggestion_vote}}', 'suggestion_id');
        $this->createIndex('idx_suggestion_vote_user_id', '{{%proposal_suggestion_vote}}', 'user_id');
        $this->createIndex('uq_suggestion_vote_suggestion_user', '{{%proposal_suggestion_vote}}', ['suggestion_id', 'user_id'], true);

        $this->addForeignKey(
            'fk_suggestion_vote_suggestion_id',
            '{{%proposal_suggestion_vote}}',
            'suggestion_id',
            '{{%proposal_suggestion}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_suggestion_vote_user_id',
            '{{%proposal_suggestion_vote}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_suggestion_vote}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_suggestion_vote_suggestion_id', '{{%proposal_suggestion_vote}}');
        $this->dropForeignKey('fk_suggestion_vote_user_id', '{{%proposal_suggestion_vote}}');
        $this->dropTable('{{%proposal_suggestion_vote}}');
    }
}
