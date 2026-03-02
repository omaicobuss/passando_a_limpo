<?php

use yii\db\Migration;

class m260302_000003_fix_proposal_table_schema extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%proposal}}', true);
        if ($table === null) {
            return;
        }

        $hasDescription = isset($table->columns['description']);
        $hasUpvotes = isset($table->columns['upvotes']);
        $hasDownvotes = isset($table->columns['downvotes']);

        if (!isset($table->columns['content'])) {
            $this->addColumn('{{%proposal}}', 'content', $this->text());
        }
        if (!isset($table->columns['theme'])) {
            $this->addColumn('{{%proposal}}', 'theme', $this->string(120));
        }
        if (!isset($table->columns['fulfillment_status'])) {
            $this->addColumn('{{%proposal}}', 'fulfillment_status', $this->string(32)->notNull()->defaultValue('not_started'));
        }
        if (!isset($table->columns['score'])) {
            $this->addColumn('{{%proposal}}', 'score', $this->integer()->notNull()->defaultValue(0));
        }

        if ($hasDescription) {
            $this->execute("UPDATE {{%proposal}} SET content = description WHERE (content IS NULL OR content = '')");
        }
        if ($hasUpvotes || $hasDownvotes) {
            $up = $hasUpvotes ? 'COALESCE(upvotes,0)' : '0';
            $down = $hasDownvotes ? 'COALESCE(downvotes,0)' : '0';
            $this->execute("UPDATE {{%proposal}} SET score = {$up} - {$down} WHERE score = 0");
        }
    }

    public function safeDown()
    {
        return true;
    }
}
