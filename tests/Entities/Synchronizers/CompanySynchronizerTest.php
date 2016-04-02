<?php

namespace History\Entities\Synchronizers;

use Faker\Factory;
use History\TestCase;

class CompanySynchronizerTest extends TestCase
{
    public function testCanSynchronizeCompanies()
    {
        $faker = Factory::create();
        $name = $faker->randomNumber(5);

        $sync = new CompanySynchronizer([
            'name' => $name.' - The PHP Consulting Company',
        ]);
        $company = $sync->synchronize();

        $this->assertEquals([
            'name' => $name,
            'created_at' => '2011-01-01 01:01:01',
            'updated_at' => '2011-01-01 01:01:01',
        ], $company->toArray());
    }
}
