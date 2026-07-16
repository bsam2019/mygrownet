#!/usr/bin/env python3
import os
import re

# Files to update
files = [
    "resources/js/pages/StockFlow/Admin/Dashboard.vue",
    "resources/js/pages/StockFlow/Admin/Login.vue",
    "resources/js/pages/StockFlow/Audits/Show.vue",
    "resources/js/pages/StockFlow/Bins/Index.vue",
    "resources/js/pages/StockFlow/Companies/Create.vue",
    "resources/js/pages/StockFlow/Companies/Index.vue",
    "resources/js/pages/StockFlow/Companies/Show.vue",
    "resources/js/pages/StockFlow/Departments/Index.vue",
    "resources/js/pages/StockFlow/Inventory/Report.vue",
    "resources/js/pages/StockFlow/Invoices/Create.vue",
    "resources/js/pages/StockFlow/Invoices/Show.vue",
    "resources/js/pages/StockFlow/Items/Create.vue",
    "resources/js/pages/StockFlow/Messages/Compose.vue",
    "resources/js/pages/StockFlow/Messages/Inbox.vue",
    "resources/js/pages/StockFlow/Messages/Sent.vue",
    "resources/js/pages/StockFlow/Messages/Show.vue",
    "resources/js/pages/StockFlow/Movements/Index.vue",
    "resources/js/pages/StockFlow/PhysicalCounts/Index.vue",
    "resources/js/pages/StockFlow/PhysicalCounts/Show.vue",
    "resources/js/pages/StockFlow/Purchases/Create.vue",
    "resources/js/pages/StockFlow/Purchases/Report.vue",
    "resources/js/pages/StockFlow/Purchases/Show.vue",
    "resources/js/pages/StockFlow/Quotations/Create.vue",
    "resources/js/pages/StockFlow/Quotations/Show.vue",
    "resources/js/pages/StockFlow/Receipts/Show.vue",
    "resources/js/pages/StockFlow/Roles/Index.vue",
    "resources/js/pages/StockFlow/Sales/Report.vue",
    "resources/js/pages/StockFlow/Sales/Show.vue",
    "resources/js/pages/StockFlow/Settings/Index.vue",
    "resources/js/pages/StockFlow/Suppliers/Index.vue",
]

for filepath in files:
    if not os.path.exists(filepath):
        print(f"⚠️  File not found: {filepath}")
        continue
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Check if already has useStockflowRoute
    if 'useStockflowRoute' in content:
        print(f"✓ Already has import: {filepath}")
        continue
    
    # Check if file uses stockflow.sub routes
    if "route('stockflow.sub." not in content and 'route("stockflow.sub.' not in content:
        print(f"⊘ No stockflow routes: {filepath}")
        continue
    
    # Add import after useCurrency
    if "import { useCurrency } from '@/composables/useCurrency';" in content:
        content = content.replace(
            "import { useCurrency } from '@/composables/useCurrency';",
            "import { useCurrency } from '@/composables/useCurrency';\nimport { useStockflowRoute } from '@/composables/useStockflowRoute';"
        )
    elif "import { useCurrency" in content:
        # Find the useCurrency import line and add after it
        lines = content.split('\n')
        new_lines = []
        for line in lines:
            new_lines.append(line)
            if 'useCurrency' in line and 'import' in line:
                new_lines.append("import { useStockflowRoute } from '@/composables/useStockflowRoute';")
        content = '\n'.join(new_lines)
    else:
        # Add after last import
        lines = content.split('\n')
        new_lines = []
        last_import_idx = 0
        for i, line in enumerate(lines):
            if line.strip().startswith('import '):
                last_import_idx = i
        for i, line in enumerate(lines):
            new_lines.append(line)
            if i == last_import_idx:
                new_lines.append("import { useStockflowRoute } from '@/composables/useStockflowRoute';")
        content = '\n'.join(new_lines)
    
    # Add const { route } = useStockflowRoute(); after imports
    # Find where to add it (after const { formatCurrency } = useCurrency(); if exists)
    if 'const { formatCurrency } = useCurrency();' in content:
        content = content.replace(
            'const { formatCurrency } = useCurrency();',
            'const { formatCurrency } = useCurrency();\nconst { route } = useStockflowRoute();'
        )
    else:
        # Find first const line and add after it
        lines = content.split('\n')
        new_lines = []
        added = False
        for line in lines:
            new_lines.append(line)
            if not added and line.strip().startswith('const ') and '<script' in '\n'.join(new_lines[:10]):
                new_lines.append('const { route } = useStockflowRoute();')
                added = True
        content = '\n'.join(new_lines)
    
    # Write back
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"✅ Updated: {filepath}")

print("\n🎉 Done!")
