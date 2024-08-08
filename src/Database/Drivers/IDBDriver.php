<?php

namespace Sophy\Database\Drivers;

interface IDBDriver
{

    const UTF8 = 'utf8';
    const UTF8_BIN = 'utf8_bin';
    const UTF8_UNICODE_CI = 'utf8_unicode_ci';
    const UTF8_GENERAL_CI = 'utf8_general_ci';

    const UTF8MB4 = 'utf8mb4';
    const UTF8MB4_BIN = 'utf8mb4_bin';
    const UTF8MB4_UNICODE_CI = 'utf8mb4_unicode_ci';
    const UTF8MB4_GENERAL_CI = 'utf8mb4_general_ci';

    const DEFAULT_HOST = 'localhost';
    const DEFAULT_PORT = 3306;
    const DEFAULT_CHARSET = 'utf8mb4';
    const DEFAULT_COLLATION = 'utf8_unicode_ci';
    const DEFAULT_DRIVER = 'mysql';

    public function connect();

    public function lastInsertId();

    public function close();

    public function statement(string $query, array $bind = []): mixed;
}
