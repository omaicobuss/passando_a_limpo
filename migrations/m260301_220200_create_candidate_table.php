<?php

use yii\db\Migration;

class m260301_220200_create_candidate_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%candidate}}', true) !== null) {
            return;
        }

        $this->createTable('{{%candidate}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'election_id' => $this->integer()->notNull(),
            'display_name' => $this->string()->notNull(),
            'bio' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_candidate_user_id', '{{%candidate}}', 'user_id');
        $this->createIndex('idx_candidate_election_id', '{{%candidate}}', 'election_id');
        $this->createIndex('uq_candidate_user_election', '{{%candidate}}', ['user_id', 'election_id'], true);

        $this->addForeignKey(
            'fk_candidate_user_id',
            '{{%candidate}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_candidate_election_id',
            '{{%candidate}}',
            'election_id',
            '{{%election}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%candidate}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_candidate_user_id', '{{%candidate}}');
        $this->dropForeignKey('fk_candidate_election_id', '{{%candidate}}');
        $this->dropTable('{{%candidate}}');
    }
}
