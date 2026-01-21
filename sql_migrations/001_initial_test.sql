-- 001_initial_test.sql
CREATE TABLE IF NOT EXISTS migration_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO migration_test (test_value) VALUES ('Hello from GitHub Actions!');
