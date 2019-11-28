<?php

namespace mipotech\requestlogger\storage;

use yii\base\InvalidConfigException;
use mipotech\requestlogger\models\RequestLog;

use Aws\Exception\AwsException;
use Aws\Kinesis\KinesisClient;

/**
 * Requires the AWS PHP SDK 3.x
 * composer require aws/aws-sdk-php
 * @link https://github.com/aws/aws-sdk-php
 *
 * For authentication, you must configure either a profile name
 * or an access key along with a secret key.
 */
class KinesisDataStream extends BaseStorage
{
    public $accessKey = '';
    public $partitionKey = '';
    public $profile = '';
    public $region = 'eu-central-1';
    public $secretKey = '';
    public $streamName = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->accessKey) && empty($this->secretKey) && empty($this->profile)) {
            throw new InvalidConfigException('Plese specify either an access key and secret key or a profile name');
        }
        if (empty($this->streamName)) {
            throw new InvalidConfigException('No stream name specified!');
        }
    }

    /**
     * @inheritdoc
     */
    protected function saveInternal(RequestLog $model): bool
    {
        $clientConfig = [
            'region'  => $this->region,
            'version' => 'latest',
        ];
        if (!empty($this->profile)) {
            $clientConfig['profile'] = $this->profile;
        } elseif (!empty($this->accessKey) && !empty($this->secretKey)) {
            $clientConfig['credentials'] = [
                'key' => $this->accessKey,
                'secret' => $this->secretKey,
            ];
        }

        $client = new KinesisClient($clientConfig);
        try {
            $res = $client->PutRecord([
                'Data' => json_encode($model->toArray()),
                'StreamName' => $this->streamName,
                'PartitionKey' => $this->partitionKey,
            ]);
        } catch (AwsException $e) {
            return false;
        }

        $this->insertId = $res->get('SequenceNumber');
        return true;
    }
}
