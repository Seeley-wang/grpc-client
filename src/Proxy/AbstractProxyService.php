<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GrpcClient\Proxy;

use Hyperf\GrpcClient\BaseClient;
use Psr\Container\ContainerInterface;

abstract class AbstractProxyService
{
    /**
     * @var BaseClient
     */
    protected $client;

    public function __construct(ContainerInterface $container, string $serviceName,string $hostname, array $options = [])
    {
        $this->client = make(BaseClient::class, [
            $serviceName,
            $hostname,
            $options
        ]);
    }
}
