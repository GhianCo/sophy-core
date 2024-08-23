<?php

namespace Sophy\Database;

use Serializable;

class DSN implements Serializable
{

    public const UTF8 = 'utf8';
    public const UTF8_BIN = 'utf8_bin';
    public const UTF8_UNICODE_CI = 'utf8_unicode_ci';
    public const UTF8_GENERAL_CI = 'utf8_general_ci';

    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_PORT = 3306;
    private const DEFAULT_CHARSET = 'utf8mb4';
    private const DEFAULT_DRIVER = 'mysql';

    /**
     * The encrypted dsnString closure
     *
     * @var \Closure
     */
    private $dsnString;

    /**
     * The Constructor
     *
     * @param callable $closure
     */
    private function __construct(callable $closure)
    {
        $this->dsnString = $closure;
    }

    /**
     * Invoke the dsnString closure
     *
     * @return array
     */
    public function __invoke()
    {
        $closure = $this->dsnString;
        return $closure();
    }

    /**
     * Serialize the dsnString object
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->__invoke());
    }

    /**
     * Unserialize a dsnString object
     *
     * @param string $serialized
     * @return dsnString
     */
    public function unserialize($serialized)
    {
        return $this;
    }

    /**
     * Factory a dsnString object
     */
    public static function factory(
        $db_name,
        $host = self::DEFAULT_HOST,
        $driver = self::DEFAULT_DRIVER,
        $port = self::DEFAULT_PORT,
        $charset = self::DEFAULT_CHARSET
    ) {
        return new self(function () use ($db_name, $host, $driver, $port, $charset) {
            $dsn = "$driver:host=$host";

            if ($port != false && !empty($port)) {
                $dsn .= ":$port";
            }

            $dsn .= ';';

            $dsn .= "dbname=$db_name;";

            if (strtolower($driver) ==  'sqlsrv') {
                $dsn = str_replace("dbname", ':database', str_replace(":host", ':server', $dsn));
            } else{
                $dsn .= "charset=$charset;";
            }

            return $dsn;
        });
    }
}
