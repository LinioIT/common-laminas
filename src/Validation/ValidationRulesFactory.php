<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Validation;

use Linio\Common\Laminas\Exception\Base\NotFoundException;
use Psr\Container\ContainerInterface;

class ValidationRulesFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws NotFoundException
     */
    public function make(string $validationClass): ValidationRules
    {
        if ($this->container->has($validationClass)) {
            return $this->container->get($validationClass);
        }

        if (!class_exists($validationClass)) {
            throw new NotFoundException(sprintf('Validation rules not found [%s]', $validationClass));
        }

        return new $validationClass();
    }
}
