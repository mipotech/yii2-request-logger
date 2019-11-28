<?php

namespace mipotech\requestlogger\storage;

use Yii;
use MongoDB\BSON\UTCDateTime;

/**
 * MongoDB storage class
 *
 * @author Chaim Leichman, MIPO Technologies LTD
 */
class Mongo extends BaseStorage
{
    /**
     * @var string
     */
    public $db = 'mongodb';
    /**
     * @var string
     */
    public $collection = 'request_log';

    /**
     * @inheritdoc
     */
    protected function saveInternal(SystemLog $model): bool
    {
        // override default datetime value with a MongoDB datetime
        $model->datetime = new UTCDateTime(round(microtime(true) * 1000));

        $collection = Yii::$app->{$this->id}->getCollection($this->collection);
        $this->insertId = $collection->insert($model->toArray());
        return !empty($this->insertId);
    }

    /**
     * @inheritdoc
     */
    public function getInsertId()
    {
        return (string)$this->insertId;
    }
}
