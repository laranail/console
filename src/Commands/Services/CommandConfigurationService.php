<?php

declare(strict_types=1);

namespace Simtabi\Laranail\ConsoleTools\Commands\Services;

/**
 * Command Configuration Service
 *
 * Provides centralized configuration access for console commands.
 * Handles different configuration namespaces and fallbacks.
 */
class CommandConfigurationService
{
    protected array $configCache = [];

    /**
     * Get configuration value with fallback
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->configCache[$key])) {
            return $this->configCache[$key];
        }

        $value = config($key, $default);
        $this->configCache[$key] = $value;

        return $value;
    }

    /**
     * Get core configuration value
     */
    public function getCore(string $key, mixed $default = null): mixed
    {
        return $this->get("laranail.core.{$key}", $default);
    }

    /**
     * Get installer configuration value
     */
    public function getInstaller(string $key, mixed $default = null): mixed
    {
        return $this->get("laranail.installer.{$key}", $default);
    }

    /**
     * Get updater configuration value
     */
    public function getUpdater(string $key, mixed $default = null): mixed
    {
        return $this->get("laranail.updater.{$key}", $default);
    }

    /**
     * Get environment value via config
     */
    public function getEnv(string $key, mixed $default = null): mixed
    {
        return $this->get("app.{$key}", $default);
    }

    /**
     * Check if configuration exists
     */
    public function has(string $key): bool
    {
        return config($key) !== null;
    }

    /**
     * Set configuration value (runtime only)
     */
    public function set(string $key, mixed $value): self
    {
        $this->configCache[$key] = $value;

        return $this;
    }

    /**
     * Clear configuration cache
     */
    public function clearCache(): self
    {
        $this->configCache = [];

        return $this;
    }

    /**
     * Get all cached configurations
     */
    public function getAllCached(): array
    {
        return $this->configCache;
    }
}
