<?php

use yii\db\Migration;

class m260301_220300_create_proposal_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal}}', [
            'id' => $this->primaryKey(),
            'election_id' => $this->integer()->notNull(),
            'candidate_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'theme' => $this->string(120),
            'content' => $this->text()->notNull(),
            'fulfillment_status' => $this->string(32)->notNull()->defaultValue('not_started'),
            'score' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_proposal_election_id', '{{%proposal}}', 'election_id');
        $this->createIndex('idx_proposal_candidate_id', '{{%proposal}}', 'candidate_id');
        $this->createIndex('idx_proposal_theme', '{{%proposal}}', 'theme');
        $this->createIndex('idx_proposal_fulfillment_status', '{{%proposal}}', 'fulfillment_status');

        $this->addForeignKey(
            'fk_proposal_election_id',
            '{{%proposal}}',
            'election_id',
            '{{%election}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_candidate_id',
            '{{%proposal}}',
            'candidate_id',
            '{{%candidate}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_proposal_election_id', '{{%proposal}}');
        $this->dropForeignKey('fk_proposal_candidate_id', '{{%proposal}}');
        $this->dropTable('{{%proposal}}');
    }
}
