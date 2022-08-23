<?php

namespace App\Enums;

use App\Models\Role;

enum Roles: int
{
	case Werewolf = 1;
	case SimpleVillager = 2;
	case Psychic = 3;
	case Witch = 4;

	public function stringify()
	{
		$name = Role::where('id', '=', $this->value)->get('display_name')->toArray()[0];
		return $name['display_name'];
	}
}
