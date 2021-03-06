<?php

/**
 * @see       https://github.com/laminas/laminas-paginator-adapter-laminasdb for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator-adapter-laminasdb/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator-adapter-laminasdb/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Paginator\Adapter\LaminasDb;

class Module
{
    /**
     * Retrieve configuration for laminas-paginator adapter plugin manager for laminas-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        return (new ConfigProvider())();
    }
}
