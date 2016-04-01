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
             ->addColumn('slug', 'string', ['null' => true])
             ->addColumn('full_name', 'string', ['null' => true])
             ->addColumn('email', 'string', ['null' => true])
             ->addColumn('contributions', 'text', ['null' => true])
             ->addColumn('yes_votes', 'integer', ['default' => 0])
             ->addColumn('no_votes', 'integer', ['default' => 0])
             ->addColumn('total_votes', 'integer', ['default' => 0])
             ->addColumn('approval', 'float', ['default' => 0])
             ->addColumn('success', 'float', ['default' => 0])
             ->addColumn('hivemind', 'float', ['default' => 0])
             ->addColumn('github_avatar', 'string', ['null' => true])
             ->addColumn('github_id', 'string', ['null' => true])
             ->addColumn('company_id', 'integer', ['null' => true])
             ->addColumn('created_at', 'datetime')
             ->addColumn('updated_at', 'datetime')
             ->addForeignKey('company_id', 'companies', 'id', ['delete' => 'CASCADE'])
             ->create();
    }
}
