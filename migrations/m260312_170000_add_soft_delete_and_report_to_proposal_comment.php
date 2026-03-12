<?php

use yii\db\Migration;

class m260312_170000_add_soft_delete_and_report_to_proposal_comment extends Migration
{
    public function safeUp()
    {
        $commentTable = $this->db->schema->getTableSchema('{{%proposal_comment}}', true);
        if ($commentTable !== null) {
            if (!isset($commentTable->columns['is_deleted'])) {
                $this->addColumn('{{%proposal_comment}}', 'is_deleted', $this->boolean()->notNull()->defaultValue(false)->after('content'));
            }

            if (!isset($commentTable->columns['deleted_at'])) {
                $this->addColumn('{{%proposal_comment}}', 'deleted_at', $this->integer()->null()->after('is_deleted'));
            }

            if (!isset($commentTable->columns['deleted_by_user_id'])) {
                $this->addColumn('{{%proposal_comment}}', 'deleted_by_user_id', $this->integer()->null()->after('deleted_at'));
                $this->createIndex('idx_proposal_comment_deleted_by_user_id', '{{%proposal_comment}}', 'deleted_by_user_id');
                $this->addForeignKey(
                    'fk_proposal_comment_deleted_by_user_id',
                    '{{%proposal_comment}}',
                    'deleted_by_user_id',
                    '{{%user}}',
                    'id',
                    'SET NULL',
                    'CASCADE'
                );
            }
        }

        if ($this->db->schema->getTableSchema('{{%proposal_comment_report}}', true) !== null) {
            return;
        }

        $this->createTable('{{%proposal_comment_report}}', [
            'id' => $this->primaryKey(),
            'comment_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'reason' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_comment_report_comment_id', '{{%proposal_comment_report}}', 'comment_id');
        $this->createIndex('idx_comment_report_user_id', '{{%proposal_comment_report}}', 'user_id');
        $this->createIndex('ux_comment_report_comment_user', '{{%proposal_comment_report}}', ['comment_id', 'user_id'], true);

        $this->addForeignKey(
            'fk_comment_report_comment_id',
            '{{%proposal_comment_report}}',
            'comment_id',
            '{{%proposal_comment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_comment_report_user_id',
            '{{%proposal_comment_report}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%proposal_comment_report}}', true) !== null) {
            $this->dropForeignKey('fk_comment_report_comment_id', '{{%proposal_comment_report}}');
            $this->dropForeignKey('fk_comment_report_user_id', '{{%proposal_comment_report}}');
            $this->dropTable('{{%proposal_comment_report}}');
        }

        $commentTable = $this->db->schema->getTableSchema('{{%proposal_comment}}', true);
        if ($commentTable === null) {
            return;
        }

        if (isset($commentTable->columns['deleted_by_user_id'])) {
            $this->dropForeignKey('fk_proposal_comment_deleted_by_user_id', '{{%proposal_comment}}');
            $this->dropIndex('idx_proposal_comment_deleted_by_user_id', '{{%proposal_comment}}');
            $this->dropColumn('{{%proposal_comment}}', 'deleted_by_user_id');
        }

        if (isset($commentTable->columns['deleted_at'])) {
            $this->dropColumn('{{%proposal_comment}}', 'deleted_at');
        }

        if (isset($commentTable->columns['is_deleted'])) {
            $this->dropColumn('{{%proposal_comment}}', 'is_deleted');
        }
    }
}