<?php

namespace Tests\Unit\Models;

use App\Models\ObjectData;
use Tests\TestCase;

class ObjectDataTest extends TestCase
{
    public function testGettingAllData()
    {
        $data = [
            'key' => 'value',
        ];
        $entity = new ObjectData($data);

        $this->assertSame($data, $entity->all());
    }

    public function testSettingKey()
    {
        $entity = new ObjectData([]);
        $entity->setKeys(['key']);
        $this->assertSame([], $entity->all());

        $entity->set('key', 'value');
        $this->assertSame(['key' => 'value'], $entity->all());
    }

    public function testSettingInexistantKey()
    {
        $entity = new ObjectData([]);
        $this->assertSame([], $entity->all());

        $entity->set('key', 'value');
        $this->assertSame([], $entity->all());
    }
}
