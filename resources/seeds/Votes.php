<?php

use History\Entities\Models\Vote;
use Phinx\Seed\AbstractSeed;

class Votes extends AbstractSeed
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
        Vote::seed(200);
    }
}
