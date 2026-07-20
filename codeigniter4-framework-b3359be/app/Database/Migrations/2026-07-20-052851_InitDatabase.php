<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitDatabase extends Migration
{
   public function up()
{
    $filePath = ROOTPATH . 'base.sql';

    if (file_exists($filePath)) {
        $sql = file_get_contents($filePath);
        
        // 1. On supprime proprement tous les commentaires SQL (-- ...) pour éviter le bug SQLite
        $sql = preg_replace('/--.*\n/', '', $sql);
        
        // 2. On découpe par point-virgule
        $queries = explode(';', $sql);
        
        foreach ($queries as $query) {
            $trimmedQuery = trim($query);
            
            if (!empty($trimmedQuery)) {
                $this->db->query($trimmedQuery);
            }
        }
    } else {
        throw new \RuntimeException("Le fichier base.sql n'a pas été trouvé à la racine du projet : " . $filePath);
    }
}

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        
        $tables = $this->db->listTables();
        foreach ($tables as $table) {
            if ($table !== 'migrations') {
                $this->db->query("DROP TABLE IF EXISTS " . $table);
            }
        }
        
        $this->db->enableForeignKeyChecks();
    }
}