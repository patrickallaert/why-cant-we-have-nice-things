<?php
namespace History\Entities\Synchronizers;

use History\TestCase;

class CompanySynchronizerTest extends TestCase
{
    public function testCanSynchronizeCompanies()
    {
        $sync = new CompanySynchronizer([
            'name' => 'thePHP.cc - The PHP Consulting Company',
        ]);
        $company = $sync->synchronize();

        $this->assertEquals([
            'name'           => 'thePHP.cc',
            'id'             => $company->id,
            'slug'           => 'thephpcc',
            'representation' => 0.0,
            'created_at'     => '2011-01-01 01:01:01',
            'updated_at'     => '2011-01-01 01:01:01',
        ], $company->toArray());
    }
}
