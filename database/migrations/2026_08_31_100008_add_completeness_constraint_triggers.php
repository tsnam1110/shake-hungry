<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * R6 và R8 (DATA-MODEL §6) là ràng buộc XUYÊN DÒNG nên không viết được bằng CHECK:
     *   R6 — dishes.completeness_level >= 2 ⇒ có >= 1 dish_ingredients
     *   R8 — dishes.completeness_level >= 2 ⇒ có >= 3 dish_recipe_sources
     *
     * MySQL không có deferred constraint trigger (kiểm lúc COMMIT) nên cơ chế
     * được bố trí theo đúng khả năng của MySQL:
     *   - AFTER UPDATE trên dishes: khi completeness_level được nâng/sửa lúc
     *     dữ liệu con thường đã tồn tại — kiểm cả R6 lẫn R8 ngay.
     *   - AFTER DELETE / AFTER UPDATE trên dish_ingredients (R6) và
     *     dish_recipe_sources (R8): chặn việc rút dữ liệu con khỏi món Mức 2.
     *   - Thời điểm INSERT món không kiểm được (nguyên liệu luôn chèn sau) —
     *     khoảng trống này do V4 trong `lacdoi:validate-data` chốt chặn, và
     *     test R6/R8 ép kiểm bằng một UPDATE chạm vào món (xem
     *     tests/Feature/IntegrityConstraintsTest.php).
     * Khác biệt này là GIỚI HẠN ĐÃ BIẾT của cơ chế trigger trên MySQL, ghi rõ
     * ở đây và ở ARCHITECTURE §4 thay vì để người sau tưởng trigger kiểm được
     * mọi thời điểm.
     */
    public function up(): void
    {
        $this->upMysql();
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_au;
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_ad;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6_au;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6_ad;
DROP TRIGGER IF EXISTS dishes_completeness_r6_r8_au;
SQL);
    }

    private function upMysql(): void
    {
        // AFTER UPDATE trên dishes — kiểm cả R6 và R8 cho món bị sửa.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER dishes_completeness_r6_r8_au AFTER UPDATE ON dishes
FOR EACH ROW
BEGIN
    DECLARE v_ing BIGINT DEFAULT 0;
    DECLARE v_src BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    IF NEW.completeness_level >= 2 THEN
        SELECT COUNT(*) INTO v_ing FROM dish_ingredients WHERE dish_id = NEW.id;
        IF v_ing < 1 THEN
            SET v_msg = CONCAT('R6: mon ', NEW.id, ' co completeness_level >= 2 nhung khong co nguyen lieu nao');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;
        SELECT COUNT(*) INTO v_src FROM dish_recipe_sources WHERE dish_id = NEW.id;
        IF v_src < 3 THEN
            SET v_msg = CONCAT('R8: mon ', NEW.id, ' co completeness_level >= 2 nhung chi co ', v_src, ' nguon cong thuc (can >= 3)');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;
    END IF;
END;
SQL);

        // R6 — chặn xoá/chuyển nguyên liệu khỏi món Mức 2.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER dish_ingredients_completeness_r6_ad AFTER DELETE ON dish_ingredients
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_ing BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    SELECT completeness_level INTO v_level FROM dishes WHERE id = OLD.dish_id;
    IF v_level IS NOT NULL AND v_level >= 2 THEN
        SELECT COUNT(*) INTO v_ing FROM dish_ingredients WHERE dish_id = OLD.dish_id;
        IF v_ing < 1 THEN
            SET v_msg = CONCAT('R6: mon ', OLD.dish_id, ' co completeness_level >= 2 nhung khong co nguyen lieu nao');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER dish_ingredients_completeness_r6_au AFTER UPDATE ON dish_ingredients
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_ing BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    IF NEW.dish_id <> OLD.dish_id THEN
        SELECT completeness_level INTO v_level FROM dishes WHERE id = OLD.dish_id;
        IF v_level IS NOT NULL AND v_level >= 2 THEN
            SELECT COUNT(*) INTO v_ing FROM dish_ingredients WHERE dish_id = OLD.dish_id;
            IF v_ing < 1 THEN
                SET v_msg = CONCAT('R6: mon ', OLD.dish_id, ' co completeness_level >= 2 nhung khong co nguyen lieu nao');
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
            END IF;
        END IF;
    END IF;
END;
SQL);

        // R8 — chặn xoá/chuyển nguồn công thức khỏi món Mức 2.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER dish_recipe_sources_completeness_r8_ad AFTER DELETE ON dish_recipe_sources
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_src BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    SELECT completeness_level INTO v_level FROM dishes WHERE id = OLD.dish_id;
    IF v_level IS NOT NULL AND v_level >= 2 THEN
        SELECT COUNT(*) INTO v_src FROM dish_recipe_sources WHERE dish_id = OLD.dish_id;
        IF v_src < 3 THEN
            SET v_msg = CONCAT('R8: mon ', OLD.dish_id, ' co completeness_level >= 2 nhung chi co ', v_src, ' nguon cong thuc (can >= 3)');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;
    END IF;
END;
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER dish_recipe_sources_completeness_r8_au AFTER UPDATE ON dish_recipe_sources
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_src BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    IF NEW.dish_id <> OLD.dish_id THEN
        SELECT completeness_level INTO v_level FROM dishes WHERE id = OLD.dish_id;
        IF v_level IS NOT NULL AND v_level >= 2 THEN
            SELECT COUNT(*) INTO v_src FROM dish_recipe_sources WHERE dish_id = OLD.dish_id;
            IF v_src < 3 THEN
                SET v_msg = CONCAT('R8: mon ', OLD.dish_id, ' co completeness_level >= 2 nhung chi co ', v_src, ' nguon cong thuc (can >= 3)');
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
            END IF;
        END IF;
    END IF;
END;
SQL);
    }
};
