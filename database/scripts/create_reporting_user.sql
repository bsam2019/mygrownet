-- Reporting DB User (6.2.4)
-- Creates a read-only MySQL user for reporting/analytics access.
-- Run against the MyGrowNet database as a MySQL superuser/root.
--
-- Usage:
--   mysql -u root -p < database/scripts/create_reporting_user.sql
--
-- Replace placeholders before running:
--   {{REPORTING_PASSWORD}} with a strong generated password

CREATE USER IF NOT EXISTS 'mygrownet_reporting'@'%' IDENTIFIED BY '{{REPORTING_PASSWORD}}';

-- Grant read-only access to all MyGrowNet tables
GRANT SELECT ON `mygrownet`.* TO 'mygrownet_reporting'@'%';

-- Grant PROCESS for SHOW PROCESSLIST (monitoring queries)
GRANT PROCESS ON *.* TO 'mygrownet_reporting'@'%';

-- Grant REPLICATION CLIENT for SHOW MASTER STATUS / SHOW SLAVE STATUS
GRANT REPLICATION CLIENT ON *.* TO 'mygrownet_reporting'@'%';

FLUSH PRIVILEGES;
