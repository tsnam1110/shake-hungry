<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TCM-000 — nền kỹ thuật lớp Mức 3 (tính vị + ghi chú văn hoá), theo kế hoạch
 * Mức 3 đã duyệt 2026-11-01 (ke-hoach-muc-3-tcm.md §4):
 *
 *  1. `ingredient_tcm_traits` += cờ hạn chế sử dụng (chính sách lưu trữ §2 của
 *     kế hoạch: nguồn uy tín ghi "không được dùng" vẫn lưu, kèm ghi chú).
 *  2. `dish_tcm_summary` += `has_conflicts` — mâu thuẫn nhãn giữa các nguồn
 *     phải hiển thị (QT-6), nên kết quả tổng hợp phải mang cờ này.
 *  3. `dishes` += `cultural_note` (JSON {text, sources[]}), `level_basis` +
 *     `level_note` cho đường nâng bù "Mức 2 (có note)": completeness_level
 *     GIỮ NGUYÊN là số nguyên (2), căn cứ nâng nằm ở level_basis — không phá
 *     vỡ mọi truy vấn/so sánh mức hiện có.
 *  4. `fruits` += `cultural_note` (cùng định dạng với món).
 *  5. `ingredient_components` — liên kết thành phần cho nguyên liệu hỗn hợp.
 *  6. Trigger R8 (migration 100008) được tạo lại với MIỄN TRỪ cho món nâng bù:
 *     món level_basis='tcm_compensation' đạt Mức 2 bằng cổng thông tin Mức 3
 *     (V20 kiểm) chứ không bằng ≥3 nguồn công thức; giữ nguyên R6 và R8 cho
 *     mọi món còn lại.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->boolean('usage_restricted')->default(false);
            $table->text('usage_note')->nullable();
        });
        // Hạn chế sử dụng mà không ghi chú "không được dùng" thì cờ vô nghĩa.
        DB::statement("ALTER TABLE ingredient_tcm_traits ADD CONSTRAINT tcm_usage_note_check CHECK (usage_restricted = 0 OR (usage_note IS NOT NULL AND CHAR_LENGTH(TRIM(usage_note)) > 0))");

        Schema::table('dish_tcm_summary', function (Blueprint $table) {
            $table->boolean('has_conflicts')->default(false);
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->json('cultural_note')->nullable();
            $table->string('level_basis', 24)->nullable();
            $table->text('level_note')->nullable();
        });
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_level_basis_check CHECK (level_basis IS NULL OR level_basis = 'tcm_compensation')");
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_level_note_check CHECK (level_basis IS NULL OR (level_note IS NOT NULL AND CHAR_LENGTH(TRIM(level_note)) > 0))");
        // Đường nâng bù chỉ sinh đúng một trạng thái: Mức 2 (có note).
        DB::statement("ALTER TABLE dishes ADD CONSTRAINT dishes_level_basis_level_check CHECK (level_basis IS NULL OR completeness_level = 2)");

        Schema::table('fruits', function (Blueprint $table) {
            $table->json('cultural_note')->nullable();
        });

        Schema::create('ingredient_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->cascadeOnDelete();
            $table->foreignId('component_ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->unique(['ingredient_id', 'component_ingredient_id'], 'ingredient_components_pair_unique');
        });
        DB::statement('ALTER TABLE ingredient_components ADD CONSTRAINT ingredient_components_not_self CHECK (ingredient_id <> component_ingredient_id)');

        $this->recreateR8Triggers(exemptCompensation: true);
    }

    public function down(): void
    {
        $this->recreateR8Triggers(exemptCompensation: false);

        Schema::dropIfExists('ingredient_components');

        Schema::table('fruits', function (Blueprint $table) {
            $table->dropColumn('cultural_note');
        });

        DB::statement('ALTER TABLE dishes DROP CONSTRAINT dishes_level_basis_level_check');
        DB::statement('ALTER TABLE dishes DROP CONSTRAINT dishes_level_note_check');
        DB::statement('ALTER TABLE dishes DROP CONSTRAINT dishes_level_basis_check');
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn(['cultural_note', 'level_basis', 'level_note']);
        });

        Schema::table('dish_tcm_summary', function (Blueprint $table) {
            $table->dropColumn('has_conflicts');
        });

        DB::statement('ALTER TABLE ingredient_tcm_traits DROP CONSTRAINT tcm_usage_note_check');
        Schema::table('ingredient_tcm_traits', function (Blueprint $table) {
            $table->dropColumn(['usage_restricted', 'usage_note']);
        });
    }

    /**
     * Tạo lại 3 trigger có chứa kiểm R8 (bản gốc ở migration 100008), thêm
     * điều kiện miễn trừ khi món mang level_basis='tcm_compensation'.
     * R6 trong trigger dishes giữ nguyên cho mọi món.
     */
    private function recreateR8Triggers(bool $exemptCompensation): void
    {
        $dishesR8Guard = $exemptCompensation
            ? "IF (NEW.level_basis IS NULL OR NEW.level_basis <> 'tcm_compensation') THEN\n"
            : '';
        $dishesR8End = $exemptCompensation ? "\n        END IF;" : '';
        $childGuard = $exemptCompensation
            ? "AND (v_basis IS NULL OR v_basis <> 'tcm_compensation') "
            : '';

        DB::unprepared(<<<SQL
DROP TRIGGER IF EXISTS dishes_completeness_r6_r8_au;
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_ad;
DROP TRIGGER IF EXISTS dish_recipe_sources_completeness_r8_au;

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
        {$dishesR8Guard}SELECT COUNT(*) INTO v_src FROM dish_recipe_sources WHERE dish_id = NEW.id;
        IF v_src < 3 THEN
            SET v_msg = CONCAT('R8: mon ', NEW.id, ' co completeness_level >= 2 nhung chi co ', v_src, ' nguon cong thuc (can >= 3)');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;{$dishesR8End}
    END IF;
END;

CREATE TRIGGER dish_recipe_sources_completeness_r8_ad AFTER DELETE ON dish_recipe_sources
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_basis VARCHAR(24) DEFAULT NULL;
    DECLARE v_src BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    SELECT completeness_level, level_basis INTO v_level, v_basis FROM dishes WHERE id = OLD.dish_id;
    IF v_level IS NOT NULL AND v_level >= 2 {$childGuard}THEN
        SELECT COUNT(*) INTO v_src FROM dish_recipe_sources WHERE dish_id = OLD.dish_id;
        IF v_src < 3 THEN
            SET v_msg = CONCAT('R8: mon ', OLD.dish_id, ' co completeness_level >= 2 nhung chi co ', v_src, ' nguon cong thuc (can >= 3)');
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
        END IF;
    END IF;
END;

CREATE TRIGGER dish_recipe_sources_completeness_r8_au AFTER UPDATE ON dish_recipe_sources
FOR EACH ROW
BEGIN
    DECLARE v_level SMALLINT DEFAULT NULL;
    DECLARE v_basis VARCHAR(24) DEFAULT NULL;
    DECLARE v_src BIGINT DEFAULT 0;
    DECLARE v_msg VARCHAR(255);
    IF NEW.dish_id <> OLD.dish_id THEN
        SELECT completeness_level, level_basis INTO v_level, v_basis FROM dishes WHERE id = OLD.dish_id;
        IF v_level IS NOT NULL AND v_level >= 2 {$childGuard}THEN
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
