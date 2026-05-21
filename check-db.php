<?php
require_once __DIR__ . '/config/db.php';
$pdo = getDB();

// Verifică tabelele
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<h3>Tabele existente:</h3>";
foreach ($tables as $t) echo "- $t<br>";

// Verifică structura
foreach (['users', 'user_profiles'] as $table) {
    if (in_array($table, $tables)) {
        echo "<h3>Structura '$table':</h3>";
        $cols = $pdo->query("DESCRIBE $table")->fetchAll();
        foreach ($cols as $c) echo "- {$c['Field']} ({$c['Type']})<br>";
    } else {
        echo "<h3 style='color:red'>Tabela '$table' NU există!</h3>";
    }
}