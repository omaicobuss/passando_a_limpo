<?php

use yii\db\Migration;

class m260302_000007_fix_proposal_legacy_required_columns extends Migration
{
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%proposal}}', true);
        if ($table === null) {
            return;
        }

        if (isset($table->columns['user_id'])) {
            $this->execute(
                "UPDATE {{%proposal}} p
                 LEFT JOIN {{%candidate}} c ON c.id = p.candidate_id
                 SET p.user_id = c.user_id
                 WHERE p.user_id IS NULL AND c.user_id IS NOT NULL"
            );
            $this->alterColumn('{{%proposal}}', 'user_id', $this->integer()->null());
        }
    }

    public function safeDown()
    {
        return true;
    }
}
