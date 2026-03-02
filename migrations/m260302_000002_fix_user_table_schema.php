<?php

use yii\db\Migration;

class m260302_000002_fix_user_table_schema extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%user}}', true);
        if ($table === null) {
            return;
        }

        if (!isset($table->columns['role'])) {
            $this->addColumn('{{%user}}', 'role', $this->string(32)->notNull()->defaultValue('citizen'));
        }
        if (!isset($table->columns['status'])) {
            $this->addColumn('{{%user}}', 'status', $this->smallInteger()->notNull()->defaultValue(10));
        }
        if (!isset($table->columns['created_at'])) {
            $this->addColumn('{{%user}}', 'created_at', $this->integer()->notNull()->defaultValue(time()));
        }
        if (!isset($table->columns['updated_at'])) {
            $this->addColumn('{{%user}}', 'updated_at', $this->integer()->notNull()->defaultValue(time()));
        }
    }

    public function safeDown()
    {
        return true;
    }
}
