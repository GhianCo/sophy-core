<?php

namespace Sophy\Providers;

use Sophy\Database\Drivers\IDBDriver;
use SophyDB\Database\PDODriver;
use SophyDB\SophyDB;
use Sophy\Providers\IServiceProvider;

class DatabaseDriverServiceProvider implements IServiceProvider {
    public function registerServices() {
        $defaultConnection = config("database.default");
        $params = [
            'driver'   => config("database.connections." . $defaultConnection . ".driver"),
            'host'     => config("database.connections." . $defaultConnection . ".host"),
            'port'     => config("database.connections." . $defaultConnection . ".port"),
            'database' => config("database.connections." . $defaultConnection . ".name"),
            'username' => config("database.connections." . $defaultConnection . ".username"),
            'password' => config("database.connections." . $defaultConnection . ".password"),
        ];

        SophyDB::addConn($params);

        singleton(IDBDriver::class, function () use ($params) {
            return new PDODriver($params, $params['driver']);
        });
    }
}
