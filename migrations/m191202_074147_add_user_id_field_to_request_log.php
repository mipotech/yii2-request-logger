<?php

use yii\db\Migration;

/**
 * Class m191202_074147_add_user_id_field_to_request_log
 */
class m191202_074147_add_user_id_field_to_request_log extends Migration
{
    const TABLE_NAME = 'request_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, 'user_id', $this->string(60));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, 'user_id');
    }
}
