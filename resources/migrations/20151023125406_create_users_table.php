<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
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
        $this->table('users')
             ->addColumn('name', 'string', ['null' => true])
             ->addColumn('full_name', 'string', ['null' => true])
             ->addColumn('email', 'string', ['null' => true])
             ->addColumn('contributions', 'text')
             ->addColumn('yes_votes', 'integer')
             ->addColumn('no_votes', 'integer')
             ->addColumn('total_votes', 'integer')
             ->addColumn('approval', 'float')
             ->addColumn('success', 'float')
             ->addColumn('hivemind', 'float')
             ->addColumn('created_at', 'datetime')
             ->addColumn('updated_at', 'datetime')
             ->save();
    }
}
