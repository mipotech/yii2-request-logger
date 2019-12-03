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
        if (is_array($dataArr['payload'])) {
            $dataArr['payload'] = $this->sanitizeArrayKeys($dataArr['payload']);
        }
        if (is_array($dataArr['response'])) {
            $dataArr['response'] = $this->sanitizeArrayKeys($dataArr['response']);
        }

        try {
            $this->insertId = $collection->insert($dataArr);
        } catch(\Exception $ex) {
            Yii::error($ex->getMessage());
            return false;
        }
        return !empty($this->insertId);
    }

    /**
     * @inheritdoc
     */
    public function getInsertId()
    {
        return $this->insertId ? (string)$this->insertId : null;
    }

    /**
     * Sanitize data arrays to remove keys that begin with a dollar sign
     * (not allowed in MongoDB)
     *
     * @param array $arr
     * @return array
     */
    protected function sanitizeArrayKeys(array $arr): array
    {
        array_walk($arr, function ($val,$key) use (&$arr) {
            if (preg_match('/^\$/', $key)) {
                $newKey = preg_replace('/^\$/', '', $key);
                $arr[$newKey] = $arr[$key];
                unset($arr[$key]);
                $key = $newKey;
            }
            // poor man's recursion...
            if (is_array($val)) {
                $arr[$key] = $this->sanitizeArrayKeys($arr[$key]);
            }
        });
        return $arr;
    }
}
