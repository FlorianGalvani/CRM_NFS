<?php

namespace App\DataFixtures\Faker;

use Faker\Generator;

class Faker extends Generator
{
    private int $baseSeed = 0;

    public function setBaseSeed(int $seed): void
    {
        $this->baseSeed = $seed;
    }

    public function seed($seed = null): void
    {
        if (is_string($seed)) {

        }

        parent::seed($this->baseSeed + $seed);
    }

}