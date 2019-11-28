<?php

namespace mipotech\requestlogger\events;

use yii\base\Event;

class SaveEvent extends Event
{
    /**
     * @var mipotech\requestlogger\models\SystemLog;
     */
    public $model;
}
