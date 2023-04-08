<?php

namespace App\Enums;

enum Rank
{
    case Villager;
    case Healer;
    case Oracle;
    case Elder;
    case Archbishop;

    public function stringify(): string
    {
        return match ($this) {
            self::Villager => 'Villageois',
            self::Healer => 'Guérisseur',
            self::Oracle => 'Oracle',
            self::Elder => 'Ancien',
            self::Archbishop => 'Archevêque'
        };
    }

    /**
     * Determine the rank based on the elo
     */
    public static function find(int $elo): self
    {
        if ($elo < 2500) {
            return self::Villager;
        } elseif ($elo < 3000) {
            return self::Healer;
        } elseif ($elo < 4000) {
            return self::Oracle;
        } elseif ($elo < 5000) {
            return self::Elder;
        }

        return self::Archbishop;
    }
}
