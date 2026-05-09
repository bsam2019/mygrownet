<?php

/**
 * Script to add Schema::hasTable() checks to all CMS migrations
 * This prevents "table already exists" errors during deployment
 */

$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');

$fixed = 0;
$skipped = 0;
$errors = [];

foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip if not a CMS migration
    if (!str_contains($filename, 'cms_') && !str_contains($filename, 'create_cms')) {
        $skipped++;
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern to find Schema::create without hasTable check
    $pattern = '/(\s+)(\/\/[^\n]*\n\s+)?Schema::create\(\'(cms_[^\']+)\'/';
    
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    
    if (empty($matches)) {
        $skipped++;
        continue;
    }
    
    // Process matches in reverse order to maintain correct offsets
    $matches = array_reverse($matches);
    $modified = false;
    
    foreach ($matches as $match) {
        $fullMatch = $match[0][0];
        $indent = $match[1][0];
        $comment = isset($match[2][0]) ? $match[2][0] : '';
        $tableName = $match[3][0];
        $offset = $match[0][1];
        
        // Check if already has hasTable check
        $beforeMatch = substr($content, max(0, $offset - 200), 200);
        if (str_contains($beforeMatch, "hasTable('$tableName')")) {
            continue;
        }
        
        // Find the closing of this Schema::create
        $openBracePos = strpos($content, '{', $offset);
        if ($openBracePos === false) continue;
        
        // Find matching closing brace and semicolon
        $braceCount = 1;
        $pos = $openBracePos + 1;
        $closeBracePos = false;
        
        while ($pos < strlen($content) && $braceCount > 0) {
            if ($content[$pos] === '{') $braceCount++;
            if ($content[$pos] === '}') $braceCount--;
            if ($braceCount === 0) {
                $closeBracePos = $pos;
                break;
            }
            $pos++;
        }
        
        if ($closeBracePos === false) continue;
        
        // Find the semicolon after the closing brace
        $semicolonPos = strpos($content, ';', $closeBracePos);
        if ($semicolonPos === false) continue;
        
        // Build the replacement
        $replacement = $indent . $comment . "if (!Schema::hasTable('$tableName')) {\n" .
                      $indent . "    Schema::create('$tableName'";
        
        // Replace the opening
        $content = substr_replace($content, $replacement, $offset, strlen($fullMatch));
        
        // Adjust positions after first replacement
        $lengthDiff = strlen($replacement) - strlen($fullMatch);
        $closeBracePos += $lengthDiff;
        $semicolonPos += $lengthDiff;
        
        // Add closing brace for if statement
        $afterSemicolon = substr($content, $semicolonPos + 1, 10);
        $newlineAfter = str_contains($afterSemicolon, "\n") ? "" : "\n";
        
        $content = substr_replace($content, ";\n" . $indent . "}" . $newlineAfter, $semicolonPos, 1);
        
        $modified = true;
    }
    
    if ($modified && $content !== $originalContent) {
        file_put_contents($file, $content);
        echo "✓ Fixed: $filename\n";
        $fixed++;
    } else {
        $skipped++;
    }
}

echo "\n";
echo "Summary:\n";
echo "  Fixed: $fixed files\n";
echo "  Skipped: $skipped files\n";
if (!empty($errors)) {
    echo "  Errors: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "    - $error\n";
    }
}
