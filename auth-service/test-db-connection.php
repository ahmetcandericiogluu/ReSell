<?php
// Database connection test script

echo "🔍 Testing database connection...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "❌ DATABASE_URL is not set!\n";
    exit(1);
}

echo "✅ DATABASE_URL is set\n";
echo "   Length: " . strlen($databaseUrl) . " characters\n";
echo "   Prefix: " . substr($databaseUrl, 0, 30) . "...\n\n";

// Parse the URL
$parts = parse_url($databaseUrl);
echo "📋 Parsed connection details:\n";
echo "   Scheme: " . ($parts['scheme'] ?? 'N/A') . "\n";
echo "   Host: " . ($parts['host'] ?? 'N/A') . "\n";
echo "   Port: " . ($parts['port'] ?? 'N/A') . "\n";
echo "   User: " . ($parts['user'] ?? 'N/A') . "\n";
echo "   Database: " . (isset($parts['path']) ? trim($parts['path'], '/') : 'N/A') . "\n";

if (isset($parts['query'])) {
    parse_str($parts['query'], $queryParams);
    echo "   Query params: " . implode(', ', array_keys($queryParams)) . "\n";
}

echo "\n🔌 Attempting PDO connection...\n";

try {
    // Try to connect with PDO
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s;sslmode=prefer",
        $parts['host'],
        $parts['port'] ?? 5432,
        trim($parts['path'], '/')
    );
    
    echo "   DSN: $dsn\n";
    
    $pdo = new PDO(
        $dsn,
        $parts['user'],
        $parts['pass'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
    
    echo "✅ PDO connection successful!\n";
    
    // Test query
    $stmt = $pdo->query('SELECT version()');
    $version = $stmt->fetchColumn();
    echo "✅ PostgreSQL version: $version\n";
    
    exit(0);
} catch (PDOException $e) {
    echo "❌ PDO connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    exit(1);
}

