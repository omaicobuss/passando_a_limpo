<?php

use yii\db\Migration;

class m260302_000004_fix_candidate_table_schema extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%candidate}}', true);
        if ($table === null) {
            return;
        }

        $hasName = isset($table->columns['name']);

        if (!isset($table->columns['user_id'])) {
            $this->addColumn('{{%candidate}}', 'user_id', $this->integer());
        }
        if (!isset($table->columns['election_id'])) {
            $this->addColumn('{{%candidate}}', 'election_id', $this->integer());
        }
        if (!isset($table->columns['display_name'])) {
            $this->addColumn('{{%candidate}}', 'display_name', $this->string()->notNull()->defaultValue(''));
        }
        if (!isset($table->columns['bio'])) {
            $this->addColumn('{{%candidate}}', 'bio', $this->text());
        }

        if ($hasName) {
            $this->execute("UPDATE {{%candidate}} SET display_name = name WHERE (display_name IS NULL OR display_name = '')");
        }
    }

    public function safeDown()
    {
        return true;
    }
}
