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

namespace Hyperf\GrpcClient\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\GrpcClient\ProxyFactory;
use Psr\Container\ContainerInterface;

class AddConsumerDefinitionListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Automatic create proxy service definitions from services.consumers.
     *
     * @param BootApplication $event
     */
    public function process(object $event)
    {
        /** @var Container $container */
        $container = $this->container;
        if ($container instanceof Container) {
            $consumers = $container->get(ConfigInterface::class)->get('grpc.consumers', []);
            $serviceFactory = $container->get(ProxyFactory::class);
            $definitions = $container->getDefinitionSource();
            foreach ($consumers as $consumer) {
                if (empty($consumer['package'])) {
                    continue;
                }
                $serviceClass = $consumer['service'] ?? $consumer['package'];
                if (!interface_exists($serviceClass)) {
                    continue;
                }
                $definitions->addDefinition(
                    $consumer['id'] ?? $serviceClass,
                    function (ContainerInterface $container) use ($serviceFactory, $consumer, $serviceClass) {
                        $proxyClass = $serviceFactory->createProxy($serviceClass);

                        return new $proxyClass(
                            $container,
                            $consumer['package'],
                            $consumer['hostname'],
                            [
                                'load_balancer' => $consumer['load_balancer'] ?? 'random',
                                'service_interface' => $serviceClass,
                                'headers' => $container->get(ConfigInterface::class)->get('grpc.headers', ''),
                                'options' => $consumer['options']
                            ]
                        );
                    }
                );
            }
        }
    }
}
