<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    private static Generator $faker;

    protected static function faker(): Generator
    {
        return self::$faker ??= Factory::create('fr_FR');
    }
}
