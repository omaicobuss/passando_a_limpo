<?php

use yii\db\Migration;

class m260302_000005_fix_election_legacy_required_columns extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%election}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['name'])) {
            $this->execute("UPDATE {{%election}} SET name = title WHERE (name IS NULL OR name = '')");
            $this->alterColumn('{{%election}}', 'name', $this->string()->null());
        }

        if (isset($table->columns['year'])) {
            $this->execute("UPDATE {{%election}} SET year = YEAR(start_date) WHERE year IS NULL AND start_date IS NOT NULL");
            $this->alterColumn('{{%election}}', 'year', $this->integer()->null());
        }
    }

    public function safeDown()
    {
        return true;
    }
}
