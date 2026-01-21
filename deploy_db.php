<?php
// deploy_db.php
require_once 'config.php';

// 1. Security Check
$secret_token = getenv('DEPLOY_TOKEN') ?: ($_GET['token'] ?? '');
// NOTE: You must set DEPLOY_TOKEN in your ecosystem or config if not using git secrets passed via URL
// For this implementation, we expect it passed via GET parameter from GitHub Action: ?token=...

if (!$secret_token) {
    die("Access Denied: No token provided.");
}

// In a real scenario, you'd compare this against a stored secret. 
// For now, we will assume the user sets a hardcoded token in config.php OR we check against a simple logic.
// BUT, since we can't easily modify config.php on the fly without potentially breaking things,
// we will verify against a hardcoded hash or environment variable if possible.
// SIMPLIFICATION FOR USER: We will ask user to define DEPLOY_TOKEN in config.php or we just check the input.
// Let's assume the user will pass the token and we compare it to a value we set here or in config.
// better: We'll expect a constant defined in config, or we'll just define it here for now (User must change it).

// ACTUALLY, to make it secure without editing config.php, we can check a specific header or just hardcode a placeholder the user must change.
// OR, we can use a "deploy_secret.php" that is gitignored? No, we need it on server.

// Let's rely on the token passed matching a hash we desire, OR better:
// The Github Secret is passed as ?token=MY_SUPER_SECRET
// We need to know MY_SUPER_SECRET on the server.
// I'll add a check for a specific file 'deploy_token.php' which contains the secret, 
// and if it doesn't exist, I'll create it with the first token provided (Trust on First Use) - a bit risky but usable?
// NO, let's just use a hardcoded check and tell the user to change it, or easier:
// We create a file 'deploy_config.php' with the secret.

$token_file = 'deploy_token.txt';
if (!file_exists($token_file)) {
    // First run: Securely set the token? No, that opens a hole.
    // Let's just demand a hardcoded token for now that matches what we tell the user to put in GitHub.
    // I will generate a random token for them.
    
    // For this specific request, I will check if the provided token matches a hardcoded "simple" secret 
    // that the user will put in GitHub.
    // User asked to "automate".
    
    // Let's assumes the user uses the token: "AutoLote2024Deploy"
    // I will enforce this token.
}

$valid_token = 'AutoLote2024Deploy'; // CHANGE THIS IF NEEDED

if ($secret_token !== $valid_token) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied: Invalid Token.');
}

// 2. Migration System
$migration_table = "migration_history";

// Create table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS $migration_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("DB Connection/Setup Error: " . $e->getMessage());
}

// 3. Get executed migrations
$stmt = $pdo->query("SELECT filename FROM $migration_table");
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 4. Scan files
$files = glob(__DIR__ . '/sql_migrations/*.sql');
sort($files); // Ensure order (001, 002...)

$executed_count = 0;

foreach ($files as $file) {
    $basename = basename($file);
    if (!in_array($basename, $executed)) {
        // Run it
        $sql = file_get_contents($file);
        
        try {
            $pdo->beginTransaction();
            $pdo->exec($sql);
            $stmt = $pdo->prepare("INSERT INTO $migration_table (filename) VALUES (?)");
            $stmt->execute([$basename]);
            $pdo->commit();
            echo "[OK] Executed: $basename<br>";
            $executed_count++;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "[ERROR] Failed: $basename - " . $e->getMessage() . "<br>";
            // Stop on error? Yes.
            exit(1);
        }
    }
}

if ($executed_count === 0) {
    echo "No new migrations to execute.";
} else {
    echo "Successfully executed $executed_count migrations.";
}
?>
