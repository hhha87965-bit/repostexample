<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExcelRowsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'file_id'    => ['type' => 'INT', 'unsigned' => true],
            'row_data'   => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('file_id', 'excel_files', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('excel_rows');
    }

    public function down()
    {
        $this->forge->dropTable('excel_rows');
    }
}
