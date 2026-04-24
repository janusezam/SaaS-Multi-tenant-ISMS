<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use App\Tenancy\TenantCacheManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class SafeCacheTenancyBootstrapper implements TenancyBootstrapper
{
    protected CacheManager $originalCache;

    public function __construct(protected Application $app) {}

    public function bootstrap(Tenant $tenant): void
    {
        $this->resetFacadeCache();

        if (! isset($this->originalCache)) {
            /** @var CacheManager $cache */
            $cache = $this->app['cache'];
            $this->originalCache = $cache;
        }

        $this->app->extend('cache', function () {
            return new TenantCacheManager($this->app);
        });
    }

    public function revert(): void
    {
        $this->resetFacadeCache();

        $originalCache = $this->originalCache;

        $this->app->extend('cache', function () use ($originalCache) {
            return $originalCache;
        });
    }

    /**
     * The Cache facade caches the resolved instance, so we need to clear it
     * when swapping the cache manager.
     */
    protected function resetFacadeCache(): void
    {
        Cache::clearResolvedInstances();
    }
}
