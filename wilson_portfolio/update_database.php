<?php
require_once 'config.php';

try {
    $pdo = getConnection();
    
    // Check if is_read column exists in contact_messages table
    $stmt = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Add is_read column if it doesn't exist
        $pdo->exec("ALTER TABLE contact_messages ADD COLUMN is_read BOOLEAN DEFAULT FALSE");
        echo "✅ Successfully added 'is_read' column to contact_messages table.\n";
    } else {
        echo "ℹ️  'is_read' column already exists in contact_messages table.\n";
    }
    
    echo "✅ Database update completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
