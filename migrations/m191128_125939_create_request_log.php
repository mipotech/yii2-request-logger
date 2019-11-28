<?php

use yii\db\Migration;

/**
 * Class m191128_125939_create_request_log
 */
class m191128_125939_create_request_log extends Migration
{
    const TABLE_NAME = 'request_log';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => $this->bigPrimaryKey(),
            'datetime' => $this->datetime(),
            'ip' => $this->string(50),  // 50 chars to allow for IPv6
            'url' => $this->text(),
            'request_headers' => $this->text(),
            'verb' => $this->string(10),
            'payload' => $this->text(),
            'response' => $this->text(),
            'response_code' => $this->string(10),
            'response_headers' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
