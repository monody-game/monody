<?php

namespace Tests\Unit\Facades;

use App\Facades\Redis;
use Tests\TestCase;

class RedisMockFacadeTest extends TestCase
{
    public function testSettingValue()
    {
        Redis::set('test', ['test']);
        $this->assertSame(['test'], Redis::get('test'));
        $this->assertSame(['test'], json_decode(Redis::data()['test']));
    }

    public function testExists()
    {
        $this->assertFalse(Redis::exists('anotherKey'));
        Redis::set('anotherKey', 'value');
        $this->assertTrue(Redis::exists('anotherKey'));
    }

    public function testDeletingAKey()
    {
        $this->assertFalse(Redis::exists('thisKeyWillBeDeleted'));
        Redis::set('thisKeyWillBeDeleted', 'value');
        $this->assertTrue(Redis::exists('thisKeyWillBeDeleted'));
        Redis::del('thisKeyWillBeDeleted');
        $this->assertFalse(Redis::exists('thisKeyWillBeDeleted'));
    }

    public function testScanningKeys()
    {
        $cursor = 0;
        Redis::set('key:1', true);
        Redis::set('key:2', true);
        Redis::set('key:3', true);
        Redis::set('key:4', true);
        Redis::set('key:5', true);
        Redis::set('hidden:key:1', true);
        Redis::set('hidden:key:2', true);

        $result = Redis::scan($cursor, ['MATCH' => 'key:*', 'COUNT' => 2]);

        $this->assertSame([
            0,
            [
                'key:1',
                'key:2',
            ],
        ], $result);

        $result = Redis::scan($cursor, ['MATCH' => 'key:*', 'COUNT' => 10]);

        $this->assertSame([
            0,
            [
                'key:1',
                'key:2',
                'key:3',
                'key:4',
                'key:5',
            ],
        ], $result);
    }
}
