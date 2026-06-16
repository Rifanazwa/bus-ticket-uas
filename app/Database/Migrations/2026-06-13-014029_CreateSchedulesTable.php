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
            'driver_1_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'driver_2_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'conductor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('driver_1_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('driver_2_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('conductor_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('schedules');
    }

    public function down()
    {
        $this->forge->dropTable('schedules');
    }
}
