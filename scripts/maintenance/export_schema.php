<?php
require 'core/Env.php';
Env::load('.env');
require 'core/Database.php';

$pdo = Database::connect();
$output = "-- ============================================\n";
$output .= "-- PCSHSKSN e-Portfolio Unified Production Schema\n";
$output .= "-- Version: 3.0 (Comprehensive Pre-Release Gold)\n";
$output .= "-- ============================================\n\n";
$output .= "SET NAMES utf8mb4;\n";
$output .= "SET CHARACTER SET utf8mb4;\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

// Get all tables
$tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
foreach ($tables as $t) {
    $tableName = $t[0];
    if ($tableName === 'migrations') continue;
    $output .= "-- =====================\n";
    $output .= "-- Table: {$tableName}\n";
    $output .= "-- =====================\n";
    $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
    $create = $pdo->query("SHOW CREATE TABLE `{$tableName}`")->fetch(PDO::FETCH_NUM);
    $output .= $create[1] . ";\n\n";
}

// Get all views
$views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_NUM);
foreach ($views as $v) {
    $viewName = $v[0];
    $output .= "-- =====================\n";
    $output .= "-- View: {$viewName}\n";
    $output .= "-- =====================\n";
    $output .= "DROP VIEW IF EXISTS `{$viewName}`;\n";
    $create = $pdo->query("SHOW CREATE VIEW `{$viewName}`")->fetch(PDO::FETCH_NUM);
    $output .= $create[1] . ";\n\n";
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents('database/schema.sql', $output);
echo "Successfully updated database/schema.sql with " . count($tables) . " tables and " . count($views) . " views.\n";
