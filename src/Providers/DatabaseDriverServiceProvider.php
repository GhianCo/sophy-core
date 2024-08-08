<?php

namespace Sophy\Providers;

use Sophy\Database\Drivers\IDBDriver;
use Sophy\Database\Drivers\PDODriver;
use Sophy\Providers\IServiceProvider;

class DatabaseDriverServiceProvider implements IServiceProvider {
    public function registerServices() {
        singleton(IDBDriver::class, function () {
            $defaultConnection = config("database.default");
            return new PDODriver([
                'driver'        => config("database.connections." . $defaultConnection . ".driver"),
                'host'          => config("database.connections." . $defaultConnection . ".host"),
                'port'          => config("database.connections." . $defaultConnection . ".port"),

                'database'      => config("database.connections." . $defaultConnection . ".name"),
                'username'      => config("database.connections." . $defaultConnection . ".username"),
                'password'      => config("database.connections." . $defaultConnection . ".password"),
            ]);
        });
    }
}
