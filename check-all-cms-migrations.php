<?php

/**
 * Script to check all CMS migrations for proper hasTable and hasColumn guards
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*cms*.php');

$issues = [];
$fixed = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);
    
    // Check for Schema::create without hasTable check
    if (preg_match('/Schema::create\([\'"]([^\'\"]+)[\'"]/', $content, $matches)) {
        $tableName = $matches[1];
        
        // Check if it has hasTable guard
        if (!preg_match('/if\s*\(\s*!\s*Schema::hasTable\([\'"]' . preg_quote($tableName, '/') . '[\'"]\)/', $content)) {
            $issues[] = [
                'file' => $filename,
                'type' => 'missing_hasTable',
                'table' => $tableName,
                'line' => 'Schema::create without hasTable check'
            ];
        }
    }
    
    // Check for Schema::table adding columns without hasColumn check
    if (preg_match_all('/Schema::table\([\'"]([^\'\"]+)[\'"],\s*function\s*\([^)]+\)\s*{([^}]+)}/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $tableName = $match[1];
            $tableContent = $match[2];
            
            // Find column additions
            if (preg_match_all('/\$table->(\w+)\([\'"]([^\'\"]+)[\'"]/', $tableContent, $columnMatches, PREG_SET_ORDER)) {
                foreach ($columnMatches as $colMatch) {
                    $columnName = $colMatch[2];
                    
                    // Check if this is inside a hasColumn check
                    $pattern = '/if\s*\(\s*!\s*Schema::hasColumn\([\'"]' . preg_quote($tableName, '/') . '[\'"],\s*[\'"]' . preg_quote($columnName, '/') . '[\'"]\)/';
                    
                    if (!preg_match($pattern, $content)) {
                        // Check if the whole Schema::table is wrapped in hasTable
                        $hasTablePattern = '/if\s*\(\s*Schema::hasTable\([\'"]' . preg_quote($tableName, '/') . '[\'"]\)/';
                        
                        if (!preg_match($hasTablePattern, $content)) {
                            $issues[] = [
                                'file' => $filename,
                                'type' => 'missing_hasColumn',
                                'table' => $tableName,
                                'column' => $columnName,
                                'line' => "Column '$columnName' added without hasColumn check"
                            ];
                        }
                    }
                }
            }
        }
    }
}

// Display results
echo "=== CMS Migration Check Results ===\n\n";

if (empty($issues)) {
    echo "✅ All CMS migrations have proper guards!\n";
} else {
    echo "⚠️  Found " . count($issues) . " potential issues:\n\n";
    
    $groupedIssues = [];
    foreach ($issues as $issue) {
        $groupedIssues[$issue['file']][] = $issue;
    }
    
    foreach ($groupedIssues as $file => $fileIssues) {
        echo "📄 $file\n";
        foreach ($fileIssues as $issue) {
            if ($issue['type'] === 'missing_hasTable') {
                echo "   ❌ Table '{$issue['table']}': {$issue['line']}\n";
            } else {
                echo "   ⚠️  Table '{$issue['table']}', Column '{$issue['column']}': {$issue['line']}\n";
            }
        }
        echo "\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "Total files checked: " . count($files) . "\n";
    echo "Files with issues: " . count($groupedIssues) . "\n";
    echo "Total issues: " . count($issues) . "\n";
}
