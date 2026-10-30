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
     * PostgreSQL (bản đầu): CONSTRAINT TRIGGER DEFERRABLE INITIALLY DEFERRED —
     * kiểm lúc COMMIT, để seeder chèn món trước rồi chèn nguyên liệu/nguồn sau
     * trong cùng transaction.
     *
     * MySQL (nền tảng chính từ 2026-10-30) KHÔNG có deferred trigger, nên cơ
     * chế tương đương được bố trí lại theo đúng khả năng của MySQL:
     *   - AFTER UPDATE trên dishes: khi completeness_level được nâng/sửa lúc
     *     dữ liệu con thường đã tồn tại — kiểm cả R6 lẫn R8 ngay.
     *   - AFTER DELETE / AFTER UPDATE trên dish_ingredients (R6) và
     *     dish_recipe_sources (R8): chặn việc rút dữ liệu con khỏi món Mức 2.
     *   - Thời điểm INSERT món không kiểm được (nguyên liệu luôn chèn sau) —
     *     khoảng trống này do V4 trong `lacdoi:validate-data` chốt chặn, và
     *     test R6/R8 ép kiểm bằng một UPDATE chạm vào món (xem
     *     tests/Feature/IntegrityConstraintsTest.php).
     * Khác biệt này là GIỚI HẠN ĐÃ BIẾT của bản MySQL, ghi rõ ở đây và ở
     * ARCHITECTURE §4 thay vì để người sau tưởng hai nền tảng giống hệt nhau.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            $this->upPgsql();

            return;
        }

        $this->upMysql();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8 ON dish_recipe_sources;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6 ON dish_ingredients;
DROP TRIGGER IF EXISTS dishes_completeness_r6_r8 ON dishes;
DROP FUNCTION IF EXISTS trg_dish_completeness_on_children();
DROP FUNCTION IF EXISTS trg_dish_completeness_on_dishes();
DROP FUNCTION IF EXISTS assert_dish_completeness(bigint);
SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_au;
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_ad;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6_au;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6_ad;
DROP TRIGGER IF EXISTS dishes_completeness_r6_r8_au;
SQL);
    }

    private function upPgsql(): void
    {
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION assert_dish_completeness(p_dish_id bigint) RETURNS void AS $$
DECLARE
    v_level smallint;
    v_ing_count bigint;
    v_src_count bigint;
BEGIN
    SELECT completeness_level INTO v_level FROM dishes WHERE id = p_dish_id;
    IF v_level IS NULL OR v_level < 2 THEN
        RETURN;
    END IF;

    SELECT count(*) INTO v_ing_count FROM dish_ingredients WHERE dish_id = p_dish_id;
    IF v_ing_count < 1 THEN
        RAISE EXCEPTION 'R6: mon % co completeness_level >= 2 nhung khong co nguyen lieu nao', p_dish_id;
    END IF;

    SELECT count(*) INTO v_src_count FROM dish_recipe_sources WHERE dish_id = p_dish_id;
    IF v_src_count < 3 THEN
        RAISE EXCEPTION 'R8: mon % co completeness_level >= 2 nhung chi co % nguon cong thuc (can >= 3)', p_dish_id, v_src_count;
    END IF;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trg_dish_completeness_on_dishes() RETURNS trigger AS $$
BEGIN
    PERFORM assert_dish_completeness(NEW.id);
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trg_dish_completeness_on_children() RETURNS trigger AS $$
BEGIN
    PERFORM assert_dish_completeness(COALESCE(OLD.dish_id, NEW.dish_id));
    IF TG_OP = 'UPDATE' AND NEW.dish_id IS DISTINCT FROM OLD.dish_id THEN
        PERFORM assert_dish_completeness(NEW.dish_id);
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE CONSTRAINT TRIGGER dishes_completeness_r6_r8
    AFTER INSERT OR UPDATE OF completeness_level ON dishes
    DEFERRABLE INITIALLY DEFERRED
    FOR EACH ROW EXECUTE FUNCTION trg_dish_completeness_on_dishes();

CREATE CONSTRAINT TRIGGER dish_ingredients_completeness_r6
    AFTER DELETE OR UPDATE OF dish_id ON dish_ingredients
    DEFERRABLE INITIALLY DEFERRED
    FOR EACH ROW EXECUTE FUNCTION trg_dish_completeness_on_children();

CREATE CONSTRAINT TRIGGER dish_recipe_sources_completeness_r8
    AFTER DELETE OR UPDATE OF dish_id ON dish_recipe_sources
    DEFERRABLE INITIALLY DEFERRED
    FOR EACH ROW EXECUTE FUNCTION trg_dish_completeness_on_children();
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
