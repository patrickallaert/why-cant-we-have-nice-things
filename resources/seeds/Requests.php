<?php

use History\Entities\Models\Request;
use Phinx\Seed\AbstractSeed;

class Requests extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        Request::seed(50);
    }
}
