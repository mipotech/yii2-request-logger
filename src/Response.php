<?php

namespace mipotech\requestlogger;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use mipotech\requestlogger\models\RequestLog;

class Response extends \yii\web\Response
{
    /**
     * @var string[] an array of specific action IDs to log
     * If blank, all actions will be logged
     */
    public $actionIds = [];
    /**
     * @var string[] an array of specific controller IDs to log
     * If blank, all controllers will be logged
     */
    public $controllerIds = [];
    /**
     * @var string[] an array of controller types for which to enable logging
     * If blank, all controller types will be logged
     */
    public $controllerTypes = ['yii\rest\Controller'];
    /**
     * @var string[] an array of environments for which to enable logging
     * If blank, all environments will be logged
     */
    public $environments = [];
    /**
     * @var string[] an array of IPs for which to enable logging
     * If blank, all IPs will be logged
     */
    public $exludeIps = [];
    /**
     * @var string[] an array of paths to exclude
     * Exact string or wildcard
     * @see https://www.php.net/manual/en/function.fnmatch.php
     */
    public $excludePaths = [];
    /**
     * @var string
     */
    public $storageClass = 'mipotech\requestlogger\storage\Mongo';
    /**
     * @var array
     */
    public $storageConfig = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // If we passed all of the restriction checks...
        $this->on(static::EVENT_BEFORE_SEND, [$this, 'handleBeforeSend']);
    }

    /**
     * @param yii\base\Event $event
     */
    public function handleBeforeSend($event)
    {
        if (!$this->isLogRequest()) {
            return;
        }

        // Verify and instantiate the storage class
        if (!class_exists($this->storageClass)) {
            throw new InvalidConfigException("Storage class {$this->storageClass} not found");
        }
        $storageObject = Yii::createObject(ArrayHelper::merge([
            'class' => $this->storageClass,
        ], $this->storageConfig));

        // Prepare the model that represents the request
        $data = $this->data;
        if (is_string($data) && is_object($tmp = json_decode($data))) {
            $data = $tmp;
        }
        $model = new RequestLog([
            'datetime' => date('Y-m-d H:i:s'),
            'ip' => Yii::$app->request->userIP,
            'url' => Yii::$app->request->absoluteUrl,
            'request_headers' => Yii::$app->request->headers->toArray(),
            'verb' => Yii::$app->request->method,
            'payload' => Yii::$app->request->bodyParams ?: null,
            'response' => $data,
            'response_code' => $this->statusCode,
            'response_headers' => $this->headers->toArray(),
            'user_id' => !empty(Yii::$app->user) && !Yii::$app->user->getIsGuest() ? Yii::$app->user->id : null,
        ]);
        if (!$model->validate()) {
            Yii::warning('RequestLog validation errors:' . PHP_EOL . print_r($model->errors, true), __CLASS__);
            return;
        }
        if ($storageObject->save($model)) {
            // If we have a record ID, add it as a header
            if ($id = $storageObject->getInsertId()) {
                $this->headers->add('request-id', (string)$id);
            } else {
                Yii::debug('No insert id returned by storage object', __CLASS__);
            }
        } else {
            Yii::warning('RequestLog record could not be saved', __CLASS__);
        }
    }

    /**
     * Determine whether this request should be logged
     *
     * @return bool
     */
    protected function isLogRequest(): bool
    {
        // Check the controller type
        if (!empty($this->controllerTypes)) {
            $log = false;
            foreach ($this->controllerTypes as $type) {
                if (Yii::$app->controller instanceof $type) {
                    $log = true;
                    break;
                }
            }
            if (!$log) {
                Yii::debug('Skipping controller type ' . get_class(Yii::$app->controller), __CLASS__);
                return false;
            }
        }

        // Do we have excluded paths?
        if (!empty($this->excludePaths)) {
            $requestPath = Yii::$app->request->url;
            foreach ($this->excludePaths as $path) {
                if (fnmatch($path, $requestPath)) {
                    Yii::debug("Skipping path {$requestPath}. Rule = {$path}", __CLASS__);
                    return false;
                }
            }
        }

        // Check other rules
        if (!empty($this->controllerIds) && !in_array(Yii::$app->controller->id, $this->controllerIds)) {
            Yii::debug('Skipping controller id ' . Yii::$app->controller->id, __CLASS__);
            return false;
        } elseif (!empty($this->actionIds) && !in_array(Yii::$app->controller->action->id, $this->actionIds)) {
            Yii::debug('Skipping action id ' . Yii::$app->controller->action->id, __CLASS__);
            return false;
        } elseif (!empty($this->environments) && defined('YII_ENV') && !in_array(YII_ENV, $this->environments)) {
            Yii::debug('Skipping environment ' . YII_ENV, __CLASS__);
            return false;
        } elseif (!empty($this->excludeIps) && in_array(Yii::$app->request->userIP, $this->excludeIps)) {
            Yii::debug('Skipping IP ' . Yii::$app->request->userIP, __CLASS__);
            return false;
        } elseif (empty($this->storageClass)) {
            Yii::warning('No storage class specified', __CLASS__);
            return false;
        }

        // If all of the checks passed...
        return true;
    }
}
