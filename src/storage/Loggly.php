<?php

namespace mipotech\requestlogger\storage;

use yii\base\InvalidConfigException;
use mipotech\requestlogger\models\RequestLog;

/**
 * Loggly storage class
 *
 * @link https://www.loggly.com/
 * @author Chaim Leichman
 */
class Loggly extends BaseStorage
{
    /**
     * @var string the Loggly
     */
    public $token = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->appId)) {
            throw new InvalidConfigException('No Loggly App ID specified!');
        }
    }

    protected function doPost(array $postData): bool
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://logs-01.loggly.com/inputs/{$this->token}/tag/http/");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $ret = curl_exec($ch);
        curl_close($ch);

        $parsedResponse = json_decode($ret, true);
        if (!empty($parsedResponse)) {
            return $parsedResponse['response'] === 'ok';
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function saveInternal(RequestLog $model): bool
    {
        return $this->doPost($model->toArray());
    }
}
