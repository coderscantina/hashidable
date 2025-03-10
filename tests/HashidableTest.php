<?php

namespace CodersCantina\Hashids;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Ramsey\Collection\Collection;

class HashidableTest extends AbstractPackageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Vinkla\Hashids\HashidsServiceProvider::class);
        $this->app['config']['hashids.connections.main.length'] = 2;
    }

    /** @test */
    public function itEncodesAnId()
    {
        $result = Foo::encodeHashId(1);

        $this->assertEquals('Za', $result);
    }

    /** @test */
    public function itDecodesAnId()
    {
        $result = Foo::decodeHashId('Za');
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function itFindsAModelByHashid()
    {
        try {
            $result = Foo::findByHashId('Za');
        } catch (QueryException $exception) {
            $this->assertEquals('select * from "foos" where "id" = ? limit 1', $exception->getSql());
            $this->assertEquals([1], $exception->getBindings());
        }
    }

    /** @test */
    public function itResolvesAModelViaRouteBinding()
    {
        try {
            $result = (new Foo)->resolveRouteBinding('Za');
        } catch (QueryException $exception) {
            $this->assertEquals('select * from "foos" where "foos"."id" = ? limit 1', $exception->getSql());
            $this->assertEquals([1], $exception->getBindings());
        }
    }

    /** @test */
    public function itGeneratesARouteKey()
    {
        $foo = (new Foo)->forceFill(['id' => 1]);

        $this->assertEquals('Za', $foo->getRouteKey());
    }

    /** @test */
    public function itReturnsNullWithANulledHashid()
    {
        $result = Foo::findByHashId(null);
        $this->assertNull($result);
    }

    /** @test */
    public function itEncodesMultipleIds()
    {
        $result = Foo::encodeHashIds([1, 2, 3]);
        $this->assertEquals(['Za', 'wy', 'Oo'], $result);
    }

    /** @test */
    public function itDecodesMultipleHashids()
    {
        $result = Foo::decodeHashIds(['Za', 'wy', 'Oo']);
        $this->assertEquals([1, 2, 3], $result);
    }

    /** @test */
    public function itHandlesEmptyArraysWhenDecoding()
    {
        $result = Foo::decodeHashIds([]);
        $this->assertEquals([], $result);
    }

    /** @test */
    public function itHandlesInvalidHashidsWhenDecoding()
    {
        $result = Foo::decodeHashIds(['invalid', 'Za', 'also-invalid']);
        $this->assertEquals([1], $result);
    }

    /** @test */
    public function itBuildsFindByHashIdsQuery()
    {
        try {
            $result = Foo::findByHashIds(['Za', 'wy']);
        } catch (QueryException $exception) {
            $this->assertEquals('select * from "foos" where "id" in (?, ?)', $exception->getSql());
            $this->assertEquals([1, 2], $exception->getBindings());
        }
    }

    /** @test */
    public function itCachesHashidsFactory()
    {
        // Call twice to verify caching works
        $factory1 = Foo::getHashidsFactory();
        $factory2 = Foo::getHashidsFactory();

        // Should be the same instance
        $this->assertSame($factory1, $factory2);
    }
}

class Foo extends Model
{
    use Hashidable;
}

class Bar extends Model
{
    use Hashidable;
}
