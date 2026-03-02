<?php

use yii\db\Migration;

class m260301_220500_create_proposal_comment_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_comment}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_comment}}', [
            'id' => $this->primaryKey(),
            'proposal_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_proposal_comment_proposal_id', '{{%proposal_comment}}', 'proposal_id');
        $this->createIndex('idx_proposal_comment_user_id', '{{%proposal_comment}}', 'user_id');
        $this->createIndex('idx_proposal_comment_parent_id', '{{%proposal_comment}}', 'parent_id');

        $this->addForeignKey(
            'fk_proposal_comment_proposal_id',
            '{{%proposal_comment}}',
            'proposal_id',
            '{{%proposal}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_comment_user_id',
            '{{%proposal_comment}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_proposal_comment_parent_id',
            '{{%proposal_comment}}',
            'parent_id',
            '{{%proposal_comment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_comment}}', true) === null) {
            return;
        }
        $this->dropForeignKey('fk_proposal_comment_proposal_id', '{{%proposal_comment}}');
        $this->dropForeignKey('fk_proposal_comment_user_id', '{{%proposal_comment}}');
        $this->dropForeignKey('fk_proposal_comment_parent_id', '{{%proposal_comment}}');
        $this->dropTable('{{%proposal_comment}}');
    }
}
