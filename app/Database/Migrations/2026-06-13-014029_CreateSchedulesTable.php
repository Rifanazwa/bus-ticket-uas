<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSchedulesTable extends Migration
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
            'route_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bus_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'departure_time' => [
                'type' => 'DATETIME',
            ],
            'arrival_time' => [
                'type' => 'DATETIME',
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'ongoing', 'completed', 'cancelled'],
                'default'    => 'scheduled',
            ],
            'driver_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'driver_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'conductor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->addForeignKey('route_id', 'routes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('bus_id', 'buses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('schedules');
    }

    public function down()
    {
        $this->forge->dropTable('schedules');
    }
}
