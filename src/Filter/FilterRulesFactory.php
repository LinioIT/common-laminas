<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Filter;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Psr\Container\ContainerInterface;

class FilterRulesFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws NotFoundException
     */
    public function make(string $filterRulesClass): mixed
    {
        if ($this->container->has($filterRulesClass)) {
            return $this->container->get($filterRulesClass);
        }

        if (!class_exists($filterRulesClass)) {
            throw new NotFoundException(sprintf('Filter rules not found [%s]', $filterRulesClass));
        }

        return new $filterRulesClass();
    }
}
