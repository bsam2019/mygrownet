#!/bin/bash
# Fix GrowBuilder page published status for site_id=2

cd /var/www/mygrownet.com

echo "Checking page status for site_id=2..."
php artisan tinker --execute="
\$page = App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', 2)->first();
if (\$page) {
    echo 'Page ID: ' . \$page->id . PHP_EOL;
    echo 'Title: ' . \$page->title . PHP_EOL;
    echo 'is_homepage: ' . (\$page->is_homepage ? 'true' : 'false') . PHP_EOL;
    echo 'is_published: ' . (\$page->is_published ? 'true' : 'false') . PHP_EOL;
    echo 'show_in_nav: ' . (\$page->show_in_nav ? 'true' : 'false') . PHP_EOL;
} else {
    echo 'No page found for site_id=2';
}
"

echo ""
echo "Updating page to published..."
php artisan tinker --execute="
App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', 2)->update(['is_published' => true, 'show_in_nav' => true]);
echo 'Done!';
"

echo ""
echo "Verifying update..."
php artisan tinker --execute="
\$page = App\Infrastructure\GrowBuilder\Models\GrowBuilderPage::where('site_id', 2)->first();
if (\$page) {
    echo 'is_published: ' . (\$page->is_published ? 'true' : 'false') . PHP_EOL;
}
"

echo ""
echo "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "Done! Try accessing https://ndelimas.mygrownet.com/ now"
