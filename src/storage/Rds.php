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
class Rds extends BaseStorage
{
    /**
     * @var string the name of the Yii application component representing the RDS connection
     */
    public $db = 'db';
    /**
     * @var null|array|false the functions used to serialize and unserialize cached data. Defaults to null, meaning
     * using the default PHP `serialize()` and `unserialize()` functions. If you want to use some more efficient
     * serializer (e.g. [igbinary](https://pecl.php.net/package/igbinary)), you may configure this property with
     * a two-element array. The first element specifies the serialization function, and the second the deserialization
     * function. If this property is set false, data will be directly sent to and retrieved from the underlying
     * cache component without any serialization or deserialization. You should not turn off serialization if
     * you are using [[Dependency|cache dependency]], because it relies on data serialization. Also, some
     * implementations of the cache can not correctly save and retrieve data different from a string type.
     * @see yii\caching\Cache
     */
    public $serializer;
    /**
     * @var string the name of the table to which to log the requests
     */
    public $table = 'request_log';

    /**
     * @inheritdoc
     */
    protected function saveInternal(RequestLog $model): bool
    {
        $command = Yii::$app->{$this->db}->createCommand();
        $recordData = $model->toArray();
        array_walk($recordData, function(&$elem) {
            if (!is_scalar($elem)) {
                $elem = $this->serialize($elem);
            }
        });
        $res = $command->insert($this->table, $recordData)->execute();
        if ($res) {
            $this->insertId = Yii::$app->{$this->db}->getLastInsertID();
        }
    }

    /**
     * Internal helper function for serializing a non-scalar value
     * @param mixed $value
     * @return string
     */
    protected function serialize($value): string
    {
        if ($value === false || $this->serializer === false) {
            return $value;
        } elseif ($this->serializer === null) {
            $value = unserialize($value);
        } else {
            $value = call_user_func($this->serializer[1], $value);
        }
        return $value;
    }
}
