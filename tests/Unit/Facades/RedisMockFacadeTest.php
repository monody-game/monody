<?php

namespace Tests\Unit\Facades;

use App\Traits\InteractsWithRedis;
use Tests\TestCase;

class RedisMockFacadeTest extends TestCase
{
    use InteractsWithRedis;

    public function testSettingValue()
    {
        $this->redis()->set('test', ['test']);
        $this->assertSame(['test'], $this->redis()->get('test'));
        $this->assertSame(['test'], json_decode($this->redis()->data['test']));
    }

    public function testExists()
    {
        $this->assertFalse($this->redis()->exists('anotherKey'));
        $this->redis()->set('anotherKey', 'value');
        $this->assertTrue($this->redis()->exists('anotherKey'));
    }

    public function testDeletingAKey()
    {
        $this->assertFalse($this->redis()->exists('thisKeyWillBeDeleted'));
        $this->redis()->set('thisKeyWillBeDeleted', 'value');
        $this->assertTrue($this->redis()->exists('thisKeyWillBeDeleted'));
        $this->redis()->del('thisKeyWillBeDeleted');
        $this->assertFalse($this->redis()->exists('thisKeyWillBeDeleted'));
    }

    public function testScanningKeys()
    {
        $cursor = 0;
        $this->redis()->set('key:1', true);
        $this->redis()->set('key:2', true);
        $this->redis()->set('key:3', true);
        $this->redis()->set('key:4', true);
        $this->redis()->set('key:5', true);
        $this->redis()->set('hidden:key:1', true);
        $this->redis()->set('hidden:key:2', true);

        $result = $this->redis()->scan($cursor, ['MATCH' => 'key:*', 'COUNT' => 2]);

        $this->assertSame([
            0,
            [
                'key:1',
                'key:2',
            ],
        ], $result);

        $result = $this->redis()->scan($cursor, ['MATCH' => 'key:*', 'COUNT' => 10]);

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
