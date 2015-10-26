<?php

use Phinx\Migration\AbstractMigration;

class CreateCommentsTable extends AbstractMigration
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
        $this->table('comments')
             ->addColumn('name', 'string')
             ->addColumn('contents', 'text')
             ->addColumn('xref', 'string')
             ->addColumn('user_id', 'integer')
             ->addColumn('request_id', 'integer')
             ->addColumn('comment_id', 'integer', ['null' => true])
             ->addColumn('created_at', 'datetime')
             ->addColumn('updated_at', 'datetime')
             ->addForeignKey('request_id', 'requests', 'id', ['delete' => 'CASCADE'])
             ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
             ->save();
    }
}
