<?php

namespace Sophy\Providers;

use Sophy\Providers\IServiceProvider;
use Sophy\View\ViewContext;
use Sophy\View\ViewStrategy;

class ViewServiceProvider implements IServiceProvider {
    public function registerServices() {
        switch (config('view.engine', 'sophy')) {
            case 'sophy':
                singleton(ViewStrategy::class, function () {
                    return new ViewContext(config("view.path"));
                });
                break;
            default:
                singleton(ViewStrategy::class, function () {
                    return new ViewContext(config("view.path"));
                });
                break;
        }
    }
}
