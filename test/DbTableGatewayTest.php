<?php

/**
 * @see       https://github.com/laminas/laminas-paginator-adapter-laminasdb for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator-adapter-laminasdb/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator-adapter-laminasdb/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\Adapter\LaminasDb;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\Sql92;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Paginator\Adapter\LaminasDb\DbSelect;
use Laminas\Paginator\Adapter\LaminasDb\DbTableGateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  Laminas\Paginator\Adapter\LaminasDb\DbTableGateway<extended>
 */
class DbTableGatewayTest extends TestCase
{
    /** @var MockObject|StatementInterface */
    protected $mockStatement;

    /** @var DbTableGateway */
    protected $dbTableGateway;

    /** @var MockObject|TableGateway */
    protected $mockTableGateway;

    public function setup(): void
    {
        $mockStatement = $this->createMock(StatementInterface::class);
        $mockDriver    = $this->createMock(DriverInterface::class);
        $mockDriver
            ->expects($this->any())
            ->method('createStatement')
            ->will($this->returnValue($mockStatement));
        $mockDriver
            ->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnArgument(0));
        $mockAdapter = $this->getMockForAbstractClass(
            Adapter::class,
            [$mockDriver, new Sql92()]
        );

        $tableName        = 'foobar';
        $mockTableGateway = $this->getMockForAbstractClass(
            TableGateway::class,
            [$tableName, $mockAdapter]
        );

        $this->mockStatement = $mockStatement;

        $this->mockTableGateway = $mockTableGateway;
    }

    public function testGetItems()
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testCount()
    {
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway);

        $mockResult = $this->createMock(ResultInterface::class);
        $mockResult
            ->expects($this->any())
            ->method('current')
            ->will($this->returnValue([DbSelect::ROW_COUNT_COLUMN_NAME => 10]));

        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $count = $this->dbTableGateway->count();
        $this->assertEquals(10, $count);
    }

    public function testGetItemsWithWhereAndOrder()
    {
        $where                = "foo = bar";
        $order                = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroup()
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            ->with(
                $this->equalTo(
                    // phpcs:ignore
                    'SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" ORDER BY "foo" ASC LIMIT limit OFFSET offset'
                )
            );
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }

    public function testGetItemsWithWhereAndOrderAndGroupAndHaving()
    {
        $where                = "foo = bar";
        $order                = "foo";
        $group                = "foo";
        $having               = "count(foo)>0";
        $this->dbTableGateway = new DbTableGateway($this->mockTableGateway, $where, $order, $group, $having);

        $mockResult = $this->createMock(ResultInterface::class);
        $this->mockStatement
            ->expects($this->once())
            ->method('setSql')
            ->with(
                $this->equalTo(
                    // phpcs:ignore
                    'SELECT "foobar".* FROM "foobar" WHERE foo = bar GROUP BY "foo" HAVING count(foo)>0 ORDER BY "foo" ASC LIMIT limit OFFSET offset'
                )
            );
        $this->mockStatement
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($mockResult));

        $items = $this->dbTableGateway->getItems(2, 10);
        $this->assertEquals([], $items);
    }
}
