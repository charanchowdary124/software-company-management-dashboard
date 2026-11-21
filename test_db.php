<?php
// test_db.php
echo "----------------------------------------\n";
echo " SAMS PHP Environment Diagnostic Tool   \n";
echo "----------------------------------------\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";

// Check PDO core
if (class_exists('PDO')) {
    echo "✓ PDO Core: Enabled\n";
} else {
    echo "✗ PDO Core: Disabled (Required for database connections)\n";
}

// Check PostgreSQL PDO driver
if (extension_loaded('pdo_pgsql')) {
    echo "✓ PDO PostgreSQL Driver (pdo_pgsql): Loaded & Active\n";
} else {
    echo "✗ PDO PostgreSQL Driver (pdo_pgsql): Not Loaded\n";
    echo "  -> Note: If you want to connect to a live Supabase database,\n";
    echo "     please open your 'php.ini' file and uncomment the line:\n";
    echo "     extension=pdo_pgsql\n";
}

echo "----------------------------------------\n";
echo "Status: Ready to run using local Mock Data fallback!\n";
echo "To start the web server, run: php -S localhost:8000\n";
echo "----------------------------------------\n";
