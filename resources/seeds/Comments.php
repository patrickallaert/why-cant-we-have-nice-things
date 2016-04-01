<?php

use History\Application;
use History\Entities\Models\Comment;
use Phinx\Seed\AbstractSeed;

class Comments extends AbstractSeed
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
        new Application();
        Comment::seed(200);
    }
}
