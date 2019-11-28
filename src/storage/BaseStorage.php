<?php

namespace mipotech\requestlogger\storage;

use yii\base\Component;
use mipotech\requestlogger\events\SaveEvent;
use mipotech\requestlogger\models\RequestLog;

/**
 * Base storage class (abstract)
 *
 * @author Chaim Leichman, MIPO Technologies LTD
 */
abstract class BaseStorage extends Component
{
    const EVENT_BEFORE_SAVE = 'beforeSave';
    const EVENT_AFTER_SAVE = 'afterSave';

    protected $insertId = null;

    /**
     * Public wrapper for the actual record saving
     *
     * @param RequestLog $model the model represting the request data
     * @return bool
     */
    public function save(RequestLog $model): bool
    {
        $event = new SaveEvent([
            'model' => $model,
        ]);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);
        $ret = $this->saveInternal($model);
        $this->trigger(self::EVENT_AFTER_SAVE, $event);
        return $ret;
    }

    /**
     * Retrieve the ID of the last inserted record
     *
     * @return int|string|null
     */
    public function getInsertId()
    {
        return $this->insertId;
    }

    /**
     * Perform the actual record saving. Implemented by each child class
     *
     * @param RequestLog $model the model represting the request data
     * @return bool
     */
    abstract protected function saveInternal(RequestLog $model): bool;
}
