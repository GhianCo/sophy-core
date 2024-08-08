<?php

namespace Sophy\Database\Drivers;

use PDO;
use Sophy\Database\DSN;

class PDODriver implements IDBDriver
{
    protected ?PDO $pdo;
    private $hasConnection = false;

    const FETCH_CLASS = \PDO::FETCH_CLASS;
    const FETCH_ASSOC = \PDO::FETCH_ASSOC;
    private $fetch = '';

    private $driver = '';
    private $host = '';
    private $port = '';

    private $charset = 'utf8mb4';
    private $collation = '';

    private $db_name = '';
    private $username = '';
    private $password = '';

    public function __construct($params = [])
    {
        $this->setDriver($params['driver'] ?? self::DEFAULT_DRIVER);
        $this->setHost($params['host'] ?? self::DEFAULT_HOST);
        $this->setPort($params['port'] ?? self::DEFAULT_PORT);

        $this->setCharset($params['charset'] ?? self::DEFAULT_CHARSET);
        $this->setCollation($params['collation'] ?? self::DEFAULT_COLLATION);
        $this->setFetch($params['fetch'] ?? self::FETCH_CLASS);

        $this->setDatabaseName($params['database'] ?? false);
        $this->setUsername($params['username'] ?? false);
        $this->setPassword($params['password'] ?? false);
    }


    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    public function setHost(string $host_address)
    {
        $this->host = $host_address;
    }

    public function setPort(string $port)
    {
        $this->port = $port;
    }

    public function setDatabaseName(string $database_name)
    {
        $this->db_name = $database_name;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }

    public function setCollation(string $collation)
    {
        $this->collation = $collation;
    }

    public function setFetch($fetch)
    {
        $this->fetch = $fetch;
    }

    public function getFetch()
    {
        return $this->fetch;
    }

    public function connect()
    {
        if (!$this->hasConnection) {
            $dsn = DSN::factory($this->db_name, $this->host, $this->driver, $this->port, $this->charset);
            $this->pdo = new \PDO((string)$dsn(), $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            $this->hasConnection =  true;
        }
    }

    /**
     * Returns the PDO object for the database connection.
     *
     * @return \PDO The PDO object for the database connection.
     *
     * @throws Exception if database settings were not made correctly and the connection was not established.
     */
    public function pdo()
    {
        if (!$this->pdo) {
            throw new \Exception("Could not establish a connection to the database. Please check your database configuration settings in config file or ensure that your database server is running");
        }

        return $this->pdo;
    }

    public function close()
    {
        $this->pdo = null;
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function statement(string $query, array $bind = []): mixed
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bind);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
