<?php

namespace App\DataFixtures\Faker;

use Faker\Generator;

class Factory
{
    public const DEFAULT_LOCALE = 'fr_FR';

    private ?int $seed;

    public function __construct(?int $seed = null)
    {
        $this->seed = $seed;
    }

    public function create(?int $seed = null, ?string $locale = self::DEFAULT_LOCALE): Generator
    {
        $generator = \Faker\Factory::create($locale);

        $faker = new Faker();
        $faker->setBaseSeed((int) $this->seed);
        foreach ($generator->getProviders() as $provider) {
            $faker->addProvider($provider);
        }
        $faker->seed((int) $seed);

        return $faker;
    }
}