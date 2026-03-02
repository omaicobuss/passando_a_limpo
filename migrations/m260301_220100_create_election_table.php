<?php

use yii\db\Migration;

class m260301_220100_create_election_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%election}}', true) !== null) {
            return;
        }

        $this->createTable('{{%election}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'start_date' => $this->date(),
            'end_date' => $this->date(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%election}}', true) === null) {
            return;
        }
        $this->dropTable('{{%election}}');
    }
}
