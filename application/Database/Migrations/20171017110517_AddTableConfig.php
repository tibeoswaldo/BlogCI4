<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_AddTableConfig extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'key' => ['type' => 'VARCHAR', 'constraint' => 250],
            'data' => ['type' => 'LONGTEXT'],
            'type' => ['type' => 'VARCHAR', 'constraint' => 250, 'default' => 'simple']
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('config');
    }

    //--------------------------------------------------------------------

    public function down()
    {
        //
    }
}
