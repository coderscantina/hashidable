<?php
namespace CodersCantina\Hashids;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class HashidableTest extends AbstractPackageTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(\Vinkla\Hashids\HashidsServiceProvider::class);
        $this->app['config']['hashids.connections.main.length'] = 2;
    }

    /** @test */
    public function it_encodes_an_id()
    {
        $result = Foo::encodeHashId(1);

        $this->assertEquals('Za', $result);
    }

    /** @test */
    public function it_decodes_an_id()
    {
        $result = Foo::decodeHashId('Za');
        $this->assertEquals(1, $result);
    }

    /** @test */
    public function it_finds_a_model_by_hashid()
    {
        try {
            $result = Foo::findByHashId('Za');
        } catch (QueryException $exception) {
            $this->assertEquals('select * from "foos" where "id" = ? limit 1', $exception->getSql());
            $this->assertEquals([1], $exception->getBindings());
        }
    }

    /** @test */
    public function it_resolves_a_model_via_route_binding()
    {
        try {
            $result = (new Foo)->resolveRouteBinding('Za');
        } catch (QueryException $exception) {
            $this->assertEquals('select * from "foos" where "foos"."id" = ? limit 1', $exception->getSql());
            $this->assertEquals([1], $exception->getBindings());
        }
    }

    /** @test */
    public function it_generates_a_route_key()
    {
        $foo = (new Foo)->forceFill(['id' => 1]);

        $this->assertEquals('Za', $foo->getRouteKey());
    }

    /** @test */
    public function it_returns_null_with_a_nulled_hashid()
    {
        $result = Foo::findByHashId(null);
        $this->assertNull($result);
    }
}

class Foo extends Model
{
    use Hashidable;
}
