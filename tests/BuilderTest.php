<?php

namespace Tests;

use Mockery;
use StdClass;
use Laravel\Scout\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class BuilderTest extends AbstractTestCase
{
    public function test_pagination_correctly_handles_paginated_results()
    {
        Paginator::currentPageResolver(function () {
            return 1;
        });
        Paginator::currentPathResolver(function () {
            return 'http://localhost/foo';
        });

        $builder = new Builder($model = Mockery::mock(), 'zonda');
        $model->shouldReceive('getPerPage')->andReturn(15);
        $model->shouldReceive('searchableUsing')->andReturn($engine = Mockery::mock());

        $engine->shouldReceive('paginate');
        $engine->shouldReceive('map')->andReturn(Collection::make([new StdClass]));
        $engine->shouldReceive('getTotalCount');

        $builder->paginate();
    }

    public function test_macroable()
    {
        Builder::macro('foo', function () {
            return 'bar';
        });

        $builder = new Builder($model = Mockery::mock(), 'zonda');
        $this->assertEquals(
            'bar', $builder->foo()
        );
    }

    public function test_where_clause_should_default_to_equal_when_operator_not_specified()
    {
        $builder = new Builder($model = Mockery::mock(), 'zonda');

        $builder->where('foo', 1);

        $expectedWhere = [
            'field' => 'foo',
            'operator' => '=',
            'value' => 1,
        ];

        $this->assertEquals($expectedWhere ,$builder->wheres[0]);
    }

    public function test_where_clause_invalid_operator_should_default_to_equal()
    {
        $builder = new Builder($model = Mockery::mock(), 'zonda');

        $builder->where('zonda', 'invalidOperator', 42);

        $expectedWhere = [
            'field' => 'zonda',
            'operator' => '=',
            'value' => 42,
        ];

        $this->assertEquals($expectedWhere ,$builder->wheres[0]);
    }

    public function test_soft_delete_sets_wheres()
    {
        $builder = new Builder($model = Mockery::mock(), 'zonda', null, true);

        $this->assertEquals(0, $builder->wheres['__soft_deleted']);
    }
}
