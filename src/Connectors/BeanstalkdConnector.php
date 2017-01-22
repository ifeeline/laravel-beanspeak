<?php
namespace Ifeeline\Beanspeak\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Support\Arr;
use Ifeeline\Beanspeak\BeanstalkdQueue;

class BeanstalkdConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
	
		$client = new \Beanspeak\Client([
			'host' => Arr::get($config, 'host', '127.0.0.1'),
			'port' => Arr::get($config, 'port', 11300),
			'timeout' => Arr::get($config, 'timeout', 60),
			'persistent' => Arr::get($config, 'persistent', true),
			'wretries' => Arr::get($config, 'wretries', 8)
		]);
		
		$client->connect();

        return new BeanstalkdQueue(
            $client, $config['queue'], Arr::get($config, 'ttr', 60)
        );
    }
}