<?php

$file = 'database/migrations/2026_05_04_100000_create_operations_module_tables.php';
$content = file_get_contents($file);

// Tables that need hasTable checks
$tables = [
    'cms_workflow_stages',
    'cms_tasks', 
    'cms_task_logs',
    'cms_task_issues',
    'cms_task_checklist_templates',
    'cms_checklist_template_items',
    'cms_task_checklist_responses',
    'cms_capacity_forecasts',
    'cms_task_recommendations',
    'cms_planning_scenarios',
    'cms_workflow_bottlenecks',
    'cms_workload_snapshots'
];

$modified = false;

foreach ($tables as $table) {
    // Check if already has hasTable
    if (str_contains($content, "hasTable('$table')")) {
        continue;
    }
    
    // Find Schema::create for this table
    $pattern = '/(\s+)(\/\/[^\n]*\n\s+)?Schema::create\(\'' . preg_quote($table) . '\'/';
    
    if (preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        $offset = $match[0][1];
        $indent = $match[1][0];
        $comment = isset($match[2][0]) ? $match[2][0] : '';
        
        // Find the closing }); for this Schema::create
        $openBracePos = strpos($content, '{', $offset);
        $braceCount = 1;
        $pos = $openBracePos + 1;
        
        while ($pos < strlen($content) && $braceCount > 0) {
            if ($content[$pos] === '{') $braceCount++;
            if ($content[$pos] === '}') $braceCount--;
            $pos++;
        }
        
        $closeBracePos = $pos - 1;
        $semicolonPos = strpos($content, ';', $closeBracePos);
        
        // Replace opening
        $replacement = $indent . $comment . "if (!Schema::hasTable('$table')) {\n" .
                      $indent . "    Schema::create('$table'";
        
        $content = substr_replace($content, $replacement, $offset, strlen($match[0][0]));
        
        // Adjust positions
        $lengthDiff = strlen($replacement) - strlen($match[0][0]);
        $semicolonPos += $lengthDiff;
        
        // Add closing brace
        $content = substr_replace($content, ";\n" . $indent . "}", $semicolonPos, 1);
        
        $modified = true;
        echo "✓ Added hasTable check for $table\n";
    }
}

if ($modified) {
    file_put_contents($file, $content);
    echo "\n✓ Fixed operations module migration\n";
} else {
    echo "No changes needed\n";
}
