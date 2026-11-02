-- Khởi tạo MySQL cho Lắc Đói (chạy 1 lần khi volume db còn trống).
-- Collation tiếng Việt theo quyết SETUP-004; log_bin_trust_function_creators
-- được đặt bằng tham số khởi động mysqld trong compose.
CREATE DATABASE IF NOT EXISTS shake_hungry
    CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
CREATE DATABASE IF NOT EXISTS shake_hungry_test
    CHARACTER SET utf8mb4 COLLATE utf8mb4_vi_0900_ai_ci;
GRANT ALL PRIVILEGES ON shake_hungry.* TO 'lakdoi'@'%';
GRANT ALL PRIVILEGES ON shake_hungry_test.* TO 'lakdoi'@'%';
