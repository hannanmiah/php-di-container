<?php

namespace Legend\Foundation;

use App\Http\Kernel;
use Legend\Container\Container;
use Psr\Container\ContainerInterface;


class Application extends Container
{
    public function __construct($basePath = null)
    {
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->registerCoreContainerAliases();
    }

    public function registerBaseBindings()
    {
    }

    public function registerBaseServiceProviders()
    {
    }

    public function registerCoreContainerAliases()
    {
        foreach ([
                     'kernel' => [\Legend\Contracts\Http\Kernel::class, Kernel::class],
                     'app' => [self::class, Container::class, ContainerInterface::class],
                 ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}