<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBusIdToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'bus_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role'
            ]
        ]);
        
        $this->db->query("ALTER TABLE users ADD CONSTRAINT fk_users_bus_id FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        try {
            $this->db->query("ALTER TABLE users DROP FOREIGN KEY fk_users_bus_id");
        } catch (\Throwable $e) {}
        
        $this->forge->dropColumn('users', 'bus_id');
    }
}
