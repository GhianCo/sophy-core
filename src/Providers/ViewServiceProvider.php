<?php

namespace Sophy\Providers;

use Sophy\Providers\IServiceProvider;
use Sophy\View\SophyEngine;
use Sophy\View\View;

class ViewServiceProvider implements IServiceProvider {
    public function registerServices() {
        switch (config('view.engine', 'sophy')) {
            case 'sophy':
                singleton(View::class, function () {
                    return new SophyEngine(config("view.path"));
                });
                break;
            default:
                singleton(View::class, function () {
                    return new SophyEngine(config("view.path"));
                });
                break;
        }
    }
}
