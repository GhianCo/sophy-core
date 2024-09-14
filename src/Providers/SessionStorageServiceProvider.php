<?php

namespace Sophy\Providers;

use Sophy\Session\PhpNativeSessionStorage;
use Sophy\Session\SessionStorage;

class SessionStorageServiceProvider implements IServiceProvider
{
    public function registerServices()
    {
        switch (config('view.storage', 'native')) {
            case 'native':
                singleton(SessionStorage::class, PhpNativeSessionStorage::class);
                break;
            default:
                singleton(SessionStorage::class, PhpNativeSessionStorage::class);
                break;
        }
    }
}
