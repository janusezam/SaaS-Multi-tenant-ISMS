<?php

declare(strict_types=1);

namespace App\Tenancy;

use Illuminate\Cache\CacheManager as BaseCacheManager;

class TenantCacheManager extends BaseCacheManager
{
    /**
     * Add tags and forward the call to the inner cache store.
     *
     * If the configured cache store does not support tags (e.g. database/file),
     * we gracefully fall back to untagged cache operations to avoid runtime
     * 500s on tenant routes.
     *
     * @param  string  $method
     * @param  array<int, mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $repository = $this->store();

        if (! $repository->supportsTags()) {
            return $repository->$method(...$parameters);
        }

        $tenantTag = config('tenancy.cache.tag_base').tenant()->getTenantKey();

        if ($method === 'tags') {
            $count = count($parameters);

            if ($count !== 1) {
                throw new \InvalidArgumentException("Method tags() takes exactly 1 argument. {$count} passed.");
            }

            $names = array_values($parameters)[0];
            $names = (array) $names;

            return $repository->tags(array_merge([$tenantTag], $names));
        }

        return $repository->tags([$tenantTag])->$method(...$parameters);
    }
}
