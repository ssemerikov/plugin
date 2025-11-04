#!/usr/bin/env php
<?php
/**
 * Check for database locks and stuck transactions
 * Run this when batch generation hangs to diagnose the issue
 */

require_once(dirname(__FILE__) . '/../../tools/bootstrap.inc.php');

echo "\n=== Database Lock Diagnostic Tool ===\n\n";

// Get database connection
$dbconn = DBConnection::getConn();

echo "1. Checking for active transactions...\n";
echo str_repeat('-', 80) . "\n";

$result = $dbconn->query("
    SELECT
        trx_id,
        trx_state,
        trx_started,
        TIMESTAMPDIFF(SECOND, trx_started, NOW()) as duration_seconds,
        trx_requested_lock_id,
        trx_wait_started,
        trx_mysql_thread_id,
        trx_query
    FROM information_schema.INNODB_TRX
    ORDER BY trx_started
");

if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        $found = true;
        echo "Transaction ID: {$row['trx_id']}\n";
        echo "  State: {$row['trx_state']}\n";
        echo "  Started: {$row['trx_started']}\n";
        echo "  Duration: {$row['duration_seconds']} seconds\n";
        echo "  Thread ID: {$row['trx_mysql_thread_id']}\n";
        if ($row['trx_wait_started']) {
            echo "  Waiting since: {$row['trx_wait_started']}\n";
        }
        if ($row['trx_query']) {
            echo "  Query: " . substr($row['trx_query'], 0, 200) . "\n";
        }
        echo "\n";
    }
    if (!$found) {
        echo "✓ No active transactions found\n\n";
    }
} else {
    echo "⚠ Could not query INNODB_TRX\n\n";
}

echo "2. Checking for locks on reviewer_certificates table...\n";
echo str_repeat('-', 80) . "\n";

$result = $dbconn->query("
    SELECT
        l.lock_id,
        l.lock_trx_id,
        l.lock_mode,
        l.lock_type,
        l.lock_table,
        l.lock_index,
        l.lock_space,
        l.lock_page,
        l.lock_rec,
        l.lock_data,
        t.trx_mysql_thread_id,
        t.trx_query,
        t.trx_state,
        TIMESTAMPDIFF(SECOND, t.trx_started, NOW()) as duration_seconds
    FROM information_schema.INNODB_LOCKS l
    LEFT JOIN information_schema.INNODB_TRX t ON l.lock_trx_id = t.trx_id
    WHERE l.lock_table LIKE '%reviewer_certificates%'
    ORDER BY l.lock_trx_id
");

if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        $found = true;
        echo "Lock ID: {$row['lock_id']}\n";
        echo "  Transaction: {$row['lock_trx_id']}\n";
        echo "  Mode: {$row['lock_mode']}\n";
        echo "  Type: {$row['lock_type']}\n";
        echo "  Table: {$row['lock_table']}\n";
        if ($row['lock_index']) {
            echo "  Index: {$row['lock_index']}\n";
        }
        if ($row['trx_mysql_thread_id']) {
            echo "  Thread ID: {$row['trx_mysql_thread_id']}\n";
        }
        if ($row['duration_seconds']) {
            echo "  Duration: {$row['duration_seconds']} seconds\n";
        }
        if ($row['trx_query']) {
            echo "  Query: " . substr($row['trx_query'], 0, 200) . "\n";
        }
        echo "\n";
    }
    if (!$found) {
        echo "✓ No locks found on reviewer_certificates table\n\n";
    }
} else {
    echo "⚠ Could not query INNODB_LOCKS\n\n";
}

echo "3. Checking for lock waits...\n";
echo str_repeat('-', 80) . "\n";

$result = $dbconn->query("
    SELECT
        w.requesting_trx_id,
        w.requested_lock_id,
        w.blocking_trx_id,
        w.blocking_lock_id,
        bt.trx_mysql_thread_id as blocking_thread,
        bt.trx_query as blocking_query,
        rt.trx_mysql_thread_id as requesting_thread,
        rt.trx_query as requesting_query,
        TIMESTAMPDIFF(SECOND, rt.trx_wait_started, NOW()) as wait_duration_seconds
    FROM information_schema.INNODB_LOCK_WAITS w
    LEFT JOIN information_schema.INNODB_TRX bt ON w.blocking_trx_id = bt.trx_id
    LEFT JOIN information_schema.INNODB_TRX rt ON w.requesting_trx_id = rt.trx_id
");

if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        $found = true;
        echo "⚠ DEADLOCK DETECTED!\n";
        echo "Requesting Transaction: {$row['requesting_trx_id']} (Thread {$row['requesting_thread']})\n";
        echo "  Waiting for: {$row['wait_duration_seconds']} seconds\n";
        if ($row['requesting_query']) {
            echo "  Query: " . substr($row['requesting_query'], 0, 200) . "\n";
        }
        echo "\nBlocking Transaction: {$row['blocking_trx_id']} (Thread {$row['blocking_thread']})\n";
        if ($row['blocking_query']) {
            echo "  Query: " . substr($row['blocking_query'], 0, 200) . "\n";
        }
        echo "\n";
    }
    if (!$found) {
        echo "✓ No lock waits detected\n\n";
    }
} else {
    echo "⚠ Could not query INNODB_LOCK_WAITS\n\n";
}

echo "4. Checking process list for long-running queries...\n";
echo str_repeat('-', 80) . "\n";

$result = $dbconn->query("
    SELECT
        ID,
        USER,
        HOST,
        DB,
        COMMAND,
        TIME,
        STATE,
        INFO
    FROM information_schema.PROCESSLIST
    WHERE TIME > 10
    ORDER BY TIME DESC
    LIMIT 20
");

if ($result) {
    $found = false;
    while ($row = $result->fetch_assoc()) {
        $found = true;
        echo "Process ID: {$row['ID']}\n";
        echo "  User: {$row['USER']}@{$row['HOST']}\n";
        echo "  Database: {$row['DB']}\n";
        echo "  Command: {$row['COMMAND']}\n";
        echo "  Duration: {$row['TIME']} seconds\n";
        echo "  State: {$row['STATE']}\n";
        if ($row['INFO']) {
            echo "  Query: " . substr($row['INFO'], 0, 200) . "\n";
        }
        echo "\n";
    }
    if (!$found) {
        echo "✓ No long-running queries (>10s) found\n\n";
    }
} else {
    echo "⚠ Could not query PROCESSLIST\n\n";
}

echo "5. Checking reviewer_certificates table status...\n";
echo str_repeat('-', 80) . "\n";

$result = $dbconn->query("
    SELECT COUNT(*) as total_certificates
    FROM reviewer_certificates
");

if ($result && $row = $result->fetch_assoc()) {
    echo "Total certificates in table: {$row['total_certificates']}\n";
} else {
    echo "⚠ Could not count certificates\n";
}

$result = $dbconn->query("SHOW TABLE STATUS LIKE 'reviewer_certificates'");
if ($result && $row = $result->fetch_assoc()) {
    echo "Engine: {$row['Engine']}\n";
    echo "Rows: {$row['Rows']}\n";
    echo "Avg row length: {$row['Avg_row_length']} bytes\n";
    echo "Data length: " . round($row['Data_length'] / 1024, 2) . " KB\n";
    echo "Index length: " . round($row['Index_length'] / 1024, 2) . " KB\n";
    echo "Auto increment: {$row['Auto_increment']}\n";
} else {
    echo "⚠ Could not get table status\n";
}

echo "\n=== Diagnostic Complete ===\n\n";

echo "RECOMMENDATIONS:\n";
echo "- If you see active transactions or locks, there may be a stuck process\n";
echo "- If you see lock waits, a deadlock is preventing the INSERT\n";
echo "- If no issues found, the problem may be connection/timeout related\n";
echo "- Try: Run batch generation again while this script is running in another terminal\n";
echo "\n";
