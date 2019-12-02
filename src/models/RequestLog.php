<?php

namespace mipotech\requestlogger\models;

use yii\base\Model;

class RequestLog extends Model
{
    /**
     * @var mixed
     */
    public $datetime;
    /**
     * @var string
     */
    public $ip;
    /**
     * @var string
     */
    public $url;
    /**
     * @var array
     */
    public $request_headers;
    /**
     * @var string
     */
    public $verb;
    /**
     * @var mixed
     */
    public $payload;
    /**
     * @var mixed
     */
    public $response;
    /**
     * @var int
     */
    public $response_code;
    /**
     * @var array
     */
    public $response_headers;
    /**
     * @var string|int
     */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['datetime', 'ip', 'url', 'verb', 'response', 'response_code', 'request_headers', 'response_headers'], 'required'],
            [['url', 'verb'], 'string'],
            [['response_code'], 'number'],
            [['payload', 'user_id'], 'safe'],
            ['ip', 'ip'],
        ];
    }
}

