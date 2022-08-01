<?php namespace Zephyrus\Tests\Database\Core;

use PHPUnit\Framework\TestCase;
use Zephyrus\Database\Core\Adapters\Mysql\MysqlAdapter;
use Zephyrus\Database\Core\Adapters\Mysql\MysqlSchemaInterrogator;
use Zephyrus\Database\Core\Adapters\Postgresql\PostgresAdapter;
use Zephyrus\Database\Core\Adapters\Postgresql\PostgresSchemaInterrogator;
use Zephyrus\Database\Core\Adapters\Sqlite\SqliteAdapter;
use Zephyrus\Database\Core\Adapters\Sqlite\SqliteSchemaInterrogator;
use Zephyrus\Database\Core\Database;
use Zephyrus\Database\Core\DatabaseSource;
use Zephyrus\Exceptions\FatalDatabaseException;

class DatabaseAdapterTest extends TestCase
{
    public function testSqlite()
    {
        $adapter = new SqliteAdapter(new DatabaseSource([
            'dbms' => 'sqlite',
            'database' => ':memory:',
            'host' => 'localhost'
        ]));
        self::assertEquals("", $adapter->getAddEnvironmentVariableClause('bob', 'lewis'));
        self::assertEquals(" LIMIT 50, 4", $adapter->getLimitClause(4, 50));
        self::assertEquals("sqlite::memory:", $adapter->getDsn());
        self::assertInstanceOf(SqliteSchemaInterrogator::class, $adapter->buildSchemaInterrogator(new Database(new DatabaseSource())));
    }

    public function testFileSqlite()
    {
        $adapter = new SqliteAdapter(new DatabaseSource([
            'dbms' => 'sqlite',
            'host' => 'localhost',
            'database' => '/lib/db.sqlite',
            'charset' => 'utf8'
        ]));
        self::assertEquals('/lib/db.sqlite', $adapter->getSource()->getDatabaseName());
        $adapter->buildConnector(); // If fails, throws an exception ...
        self::assertTrue(true);
    }

    public function testFileSqliteError()
    {
        $this->expectException(FatalDatabaseException::class);
        $this->expectExceptionCode(FatalDatabaseException::SQLITE_INVALID_DATABASE);
        $adapter = new SqliteAdapter(new DatabaseSource([
            'dbms' => 'sqlite',
            'host' => 'localhost',
            'database' => ROOT_DIR . '/lib/db.sqlidfljsdfkjlsdkfjlte'
        ]));
        $adapter->buildConnector();
    }

    public function testMySql()
    {
        $adapter = new MysqlAdapter(new DatabaseSource([
            'dbms' => 'mysql',
            'database' => 'test',
            'host' => 'localhost',
            'charset' => 'utf8'
        ]));
        $clause = $adapter->getAddEnvironmentVariableClause('bob', 'lewis');
        self::assertEquals("SET @bob = 'lewis'", $clause);
        self::assertEquals(" LIMIT 50, 4", $adapter->getLimitClause(4, 50));
        self::assertEquals("mysql:dbname=test;host=localhost;charset=utf8;", $adapter->getDsn());
        self::assertInstanceOf(MysqlSchemaInterrogator::class, $adapter->buildSchemaInterrogator(new Database(new DatabaseSource())));
    }

    public function testPgsql()
    {
        $adapter = new PostgresAdapter(new DatabaseSource([
            'dbms' => 'pgsql',
            'database' => 'test',
            'host' => 'localhost',
            'charset' => 'utf8'
        ]));
        self::assertEquals("set session \"bob\" = 'lewis';", $adapter->getAddEnvironmentVariableClause('bob', 'lewis'));
        self::assertEquals(" LIMIT 50 OFFSET 4", $adapter->getLimitClause(4, 50));
        self::assertEquals("pgsql:dbname=test;host=localhost;", $adapter->getDsn());
        self::assertInstanceOf(PostgresSchemaInterrogator::class, $adapter->buildSchemaInterrogator(new Database(new DatabaseSource())));
    }
}