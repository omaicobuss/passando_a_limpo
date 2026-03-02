<?php

use yii\db\Migration;

class m260302_000006_fix_candidate_legacy_required_columns extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%candidate}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['name'])) {
            $this->execute("UPDATE {{%candidate}} SET name = display_name WHERE (name IS NULL OR name = '')");
            $this->alterColumn('{{%candidate}}', 'name', $this->string()->null());
        }
    }

    public function safeDown()
    {
        return true;
    }
}
