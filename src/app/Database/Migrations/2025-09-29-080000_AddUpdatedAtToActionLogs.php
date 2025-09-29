<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedAtToActionLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('action_logs', [
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_at'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('action_logs', 'updated_at');
    }
}
