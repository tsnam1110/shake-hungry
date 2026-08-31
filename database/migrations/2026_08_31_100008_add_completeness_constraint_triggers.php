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
     * Dùng CONSTRAINT TRIGGER DEFERRABLE INITIALLY DEFERRED: kiểm lúc COMMIT, để seeder
     * chèn món trước rồi chèn nguyên liệu/nguồn sau trong cùng transaction.
     */
    public function up(): void
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

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8 ON dish_recipe_sources;
DROP TRIGGER IF EXISTS dish_ingredients_completeness_r6 ON dish_ingredients;
DROP TRIGGER IF EXISTS dishes_completeness_r6_r8 ON dishes;
DROP FUNCTION IF EXISTS trg_dish_completeness_on_children();
DROP FUNCTION IF EXISTS trg_dish_completeness_on_dishes();
DROP FUNCTION IF EXISTS assert_dish_completeness(bigint);
SQL);
    }
};
