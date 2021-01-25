<?php

declare(strict_types=1);

namespace Philly\ServiceProvider;

use Philly\Container\BindingContainer;
use Philly\Contracts\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Philly\Contracts\ServiceProvider\ServiceProviderContainer as ServiceProviderContainerContract;

/**
 * Class ServiceProviderContainer.
 */
class ServiceProviderContainer extends BindingContainer implements ServiceProviderContainerContract
{
    protected bool $booted = false;
    protected bool $booting = false;

    /**
     * @inheritDoc
     */
    public function acceptsBinding($value): bool
    {
        return $value instanceof ServiceProviderContract;
    }

    /**
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed|ServiceProviderContract $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if ($this->booted) {
            throw new AlreadyBootedException("Service provider container was already booted!");
        }

        parent::offsetSet($offset, $value);

        $value->onRegistered();
    }

    public function offsetGet($offset)
    {
        if (!$this->booted && !$this->booting) {
            $this->boot();
        }

        return parent::offsetGet($offset);
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        if ($this->booted || $this->booting)
            throw new AlreadyBootedException("Service provider container ".__CLASS__." was already booted.");

        $this->booting = true;

        /** @var ServiceProviderContract $service */
        foreach ($this as $service) {
            $service->onBooted();
        }

        $this->booting = false;
        $this->booted = true;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}
