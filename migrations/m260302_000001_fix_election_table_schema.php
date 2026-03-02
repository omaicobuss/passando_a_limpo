<?php

use yii\db\Migration;

class m260302_000001_fix_election_table_schema extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%election}}', true);
        if ($table === null) {
            return;
        }

        $hasName = isset($table->columns['name']);
        $hasTitle = isset($table->columns['title']);
        $hasYear = isset($table->columns['year']);

        if (!$hasTitle) {
            $this->addColumn('{{%election}}', 'title', $this->string()->notNull()->defaultValue(''));
        }

        $table = $this->db->schema->getTableSchema('{{%election}}', true);
        if (!isset($table->columns['description'])) {
            $this->addColumn('{{%election}}', 'description', $this->text());
        }
        if (!isset($table->columns['start_date'])) {
            $this->addColumn('{{%election}}', 'start_date', $this->date());
        }
        if (!isset($table->columns['end_date'])) {
            $this->addColumn('{{%election}}', 'end_date', $this->date());
        }

        if ($hasName) {
            $this->execute("UPDATE {{%election}} SET title = name WHERE (title IS NULL OR title = '')");
        }

        if ($hasYear) {
            $this->execute("UPDATE {{%election}} SET start_date = CONCAT(year, '-01-01') WHERE start_date IS NULL");
            $this->execute("UPDATE {{%election}} SET end_date = CONCAT(year, '-12-31') WHERE end_date IS NULL");
        }
    }

    public function safeDown()
    {
        return true;
    }
}
