<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookingSeatsTable extends Migration
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
            'booking_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'seat_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
            ],
            'passenger_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('booking_seats');
    }

    public function down()
    {
        $this->forge->dropTable('booking_seats');
    }
}
