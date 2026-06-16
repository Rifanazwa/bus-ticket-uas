<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromosTable extends Migration
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
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'unique'     => true,
            ],
            'discount_type' => [
                'type'       => 'ENUM',
                'constraint' => ['percent', 'fixed'],
            ],
            'discount_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'valid_from' => [
                'type' => 'DATE',
            ],
            'valid_until' => [
                'type' => 'DATE',
            ],
            'usage_limit' => [
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
        $this->forge->createTable('promos');
    }

    public function down()
    {
        $this->forge->dropTable('promos');
    }
}
