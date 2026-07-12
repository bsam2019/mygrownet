@echo off
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && php artisan tinker --execute='echo DB::select(\"SHOW TABLES LIKE \\\"sa_%\\\"\");'"
