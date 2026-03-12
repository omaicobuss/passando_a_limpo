<?php

use yii\db\Migration;
use yii\db\Query;

class m260312_150000_create_proposal_revision_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_revision}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_revision}}', [
            'id' => $this->primaryKey(),
            'proposal_id' => $this->integer()->notNull(),
            'version_number' => $this->integer()->notNull(),
            'election_id' => $this->integer()->notNull(),
            'candidate_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'theme' => $this->string(120),
            'content' => $this->text()->notNull(),
            'fulfillment_status' => $this->string(32)->notNull(),
            'edited_by_user_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('ux_proposal_revision_proposal_version', '{{%proposal_revision}}', ['proposal_id', 'version_number'], true);
        $this->createIndex('idx_proposal_revision_created_at', '{{%proposal_revision}}', 'created_at');
        $this->createIndex('idx_proposal_revision_edited_by_user_id', '{{%proposal_revision}}', 'edited_by_user_id');

        $this->addForeignKey(
            'fk_proposal_revision_proposal_id',
            '{{%proposal_revision}}',
            'proposal_id',
            '{{%proposal}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_revision_election_id',
            '{{%proposal_revision}}',
            'election_id',
            '{{%election}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_revision_candidate_id',
            '{{%proposal_revision}}',
            'candidate_id',
            '{{%candidate}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_revision_edited_by_user_id',
            '{{%proposal_revision}}',
            'edited_by_user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $proposals = (new Query())
            ->from('{{%proposal}}')
            ->all($this->db);

        foreach ($proposals as $proposal) {
            $editorId = (new Query())
                ->from('{{%candidate}}')
                ->select('user_id')
                ->where(['id' => $proposal['candidate_id']])
                ->scalar($this->db);

            $this->insert('{{%proposal_revision}}', [
                'proposal_id' => (int) $proposal['id'],
                'version_number' => 1,
                'election_id' => (int) $proposal['election_id'],
                'candidate_id' => (int) $proposal['candidate_id'],
                'title' => (string) $proposal['title'],
                'theme' => $proposal['theme'],
                'content' => (string) $proposal['content'],
                'fulfillment_status' => (string) $proposal['fulfillment_status'],
                'edited_by_user_id' => $editorId === false || $editorId === null ? null : (int) $editorId,
                'created_at' => (int) $proposal['created_at'],
            ]);
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_revision}}', true) === null) {
            return;
        }

        $this->dropForeignKey('fk_proposal_revision_edited_by_user_id', '{{%proposal_revision}}');
        $this->dropForeignKey('fk_proposal_revision_candidate_id', '{{%proposal_revision}}');
        $this->dropForeignKey('fk_proposal_revision_election_id', '{{%proposal_revision}}');
        $this->dropForeignKey('fk_proposal_revision_proposal_id', '{{%proposal_revision}}');
        $this->dropTable('{{%proposal_revision}}');
    }
}