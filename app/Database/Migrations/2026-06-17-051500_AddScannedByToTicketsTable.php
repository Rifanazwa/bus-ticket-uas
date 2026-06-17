<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScannedByToTicketsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'scanned_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status'
            ],
            'scanned_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'scanned_by'
            ]
        ]);
        
        $this->db->query("ALTER TABLE tickets ADD CONSTRAINT fk_tickets_scanned_by FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        try {
            $this->db->query("ALTER TABLE tickets DROP FOREIGN KEY fk_tickets_scanned_by");
        } catch (\Throwable $e) {}
        
        $this->forge->dropColumn('tickets', ['scanned_by', 'scanned_at']);
    }
}
