<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExcelFilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'path'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'row_count'    => ['type' => 'INT', 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('excel_files');
    }

    public function down()
    {
        $this->forge->dropTable('excel_files');
    }
}
