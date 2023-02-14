<?php

namespace App\RedisMock;

use Illuminate\Redis\Connections\PredisConnection;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Support\Arr;
use M6Web\Component\RedisMock\RedisMockFactory;
use Predis\Client;

class PredisConnectorMock extends PredisConnector
{
    /**
     * Create a new clustered Predis connection.
     */
    public function connect(array $config, array $options): PredisConnection|PredisConnectionMock
    {
        $formattedOptions = array_merge(
            ['timeout' => 10.0], $options, Arr::pull($config, 'options', [])
        );

        $factory = new RedisMockFactory();
        /** @var Client $redisMockClass */
        $redisMockClass = $factory->getAdapter('Predis\Client', true);

        return new PredisConnectionMock(new $redisMockClass($config, $formattedOptions));
    }

    /**
     * Create a new clustered Predis connection.
     *
     * @phpstan-ignore-next-line
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options): PredisConnectionMock
    {
        $clusterSpecificOptions = Arr::pull($config, 'options', []);

        $factory = new RedisMockFactory();
        /** @var Client $redisMockClass */
        $redisMockClass = $factory->getAdapter('Predis\Client', true);

        return new PredisConnectionMock(new $redisMockClass(array_values($config), array_merge(
            $options, $clusterOptions, $clusterSpecificOptions
        )));
    }
}
