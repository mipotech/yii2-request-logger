<?php

namespace mipotech\requestlogger\storage;

use Yii;
use mipotech\requestlogger\models\RequestLog;
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
    protected function saveInternal(RequestLog $model): bool
    {
        $collection = Yii::$app->{$this->db}->getCollection($this->collection);
        $dataArr = $model->toArray();
        $dataArr['datetime'] = new UTCDateTime(round(microtime(true) * 1000));
        $this->insertId = $collection->insert($dataArr);
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
