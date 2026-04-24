<?php
$db = new PDO('sqlite:database/database.sqlite');
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = $db->query("PRAGMA table_info($t)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $nn  = $c['notnull'] ? 'NOT NULL' : 'NULL';
        $dflt = $c['dflt_value'] !== null ? "DEFAULT {$c['dflt_value']}" : '';
        $pk  = $c['pk'] ? 'PK' : '';
        echo "  {$c['name']} | {$c['type']} | $nn | $dflt | $pk\n";
    }
    // foreign keys
    $fks = $db->query("PRAGMA foreign_key_list($t)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fks as $fk) {
        echo "  FK: {$fk['from']} -> {$fk['table']}.{$fk['to']} (on_delete={$fk['on_delete']})\n";
    }
    // indexes
    $idxs = $db->query("SELECT name, sql FROM sqlite_master WHERE type='index' AND tbl_name='$t'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($idxs as $idx) {
        if ($idx['sql']) echo "  IDX: {$idx['name']}: {$idx['sql']}\n";
    }
    echo "\n";
}
