<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoutesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'origin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'destination' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'distance_km' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
            ],
            'estimated_duration' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('routes');
    }

    public function down()
    {
        $this->forge->dropTable('routes');
    }
}
