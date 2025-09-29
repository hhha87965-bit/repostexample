<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActionLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'file_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action_type'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'description'  => ['type' => 'TEXT', 'null' => true],
            'user_ip'      => ['type' => 'VARCHAR', 'constraint' => 45],
            'user_agent'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('file_id', 'excel_files', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('action_logs');
    }

    public function down()
    {
        $this->forge->dropTable('action_logs');
    }
}
