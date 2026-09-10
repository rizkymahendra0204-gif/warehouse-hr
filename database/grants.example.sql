-- Template only. Replace the sample password before executing as a DBA.
-- These grants assume DB_HOST=127.0.0.1 and database=db_warehouse.
CREATE USER 'warehouse_app'@'127.0.0.1' IDENTIFIED BY 'Leo@27';
GRANT SELECT, INSERT, UPDATE ON db_warehouse.master_item TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, UPDATE ON db_warehouse.request_form TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT ON db_warehouse.transaksi TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT ON db_warehouse.transaksi_detail TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT ON db_warehouse.return_items TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT ON db_warehouse.log_activity TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON db_warehouse.users TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, UPDATE ON db_warehouse.settings TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON db_warehouse.transaction_sequences TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON db_warehouse.login_throttle TO 'warehouse_app'@'127.0.0.1';
GRANT SELECT ON db_warehouse.schema_migrations TO 'warehouse_app'@'127.0.0.1';
-- Use a separate, temporary migration account with ALTER/INDEX/CREATE privileges.
-- Runtime account intentionally cannot delete inventory/history or change schema.
