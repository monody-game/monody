<?php

namespace Tests\Unit\Models;

use App\Models\Entity;
use Tests\TestCase;

class EntityTest extends TestCase
{
	public function testGettingAllData() {
		$data = [
			'key' => 'value'
		];
		$entity = new Entity($data);

		$this->assertSame($data, $entity->all());
	}

	public function testSettingKey() {
		$entity = new Entity([]);
		$entity->setKeys(['key']);
		$this->assertSame([], $entity->all());

		$entity->set('key', 'value');
		$this->assertSame(['key' => 'value'], $entity->all());
	}

	public function testSettingInexistantKey() {
		$entity = new Entity([]);
		$this->assertSame([], $entity->all());

		$entity->set('key', 'value');
		$this->assertSame([], $entity->all());
	}
}
