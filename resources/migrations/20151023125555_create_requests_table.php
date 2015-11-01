<?php

use Phinx\Migration\AbstractMigration;

class CreateRequestsTable extends AbstractMigration
{
    /**
     * Change Method.
     * Write your reversible migrations using this method.
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('requests')
             ->addColumn('name', 'string')
             ->addColumn('slug', 'string', ['null' => true])
             ->addColumn('contents', 'text')
             ->addColumn('link', 'string')
             ->addColumn('pull_request', 'string', ['null' => true])
             ->addColumn('condition', 'text', ['null' => true])
             ->addColumn('approval', 'float')
             ->addColumn('status', 'integer')
             ->addColumn('created_at', 'datetime')
             ->addColumn('updated_at', 'datetime')
             ->create();
    }
}
