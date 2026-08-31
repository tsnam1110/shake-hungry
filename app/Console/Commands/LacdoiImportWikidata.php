<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * lacdoi:import-wikidata — DATA-005: gieo mầm danh mục món Việt từ Wikidata (CC0).
 *
 * Lưu kết quả THÔ + ngày truy vấn vào data/reference/wikidata-vn-dishes.json,
 * rồi sinh file món ở data/dishes/vn/ với status=draft, needs_review=true,
 * completeness_level=1 — dữ liệu Wikidata thưa, PHẢI qua tay người biên soạn
 * trước khi hiển thị (cạm bẫy ghi ở DATA-005).
 *
 * Ảnh: chỉ ghi URL nguồn vào ghi chú, KHÔNG tải về — license từng ảnh Commons
 * khác nhau, phải kiểm trước và chờ câu trả lời Q4 (ràng buộc R4).
 */
class LacdoiImportWikidata extends Command
{
    protected $signature = 'lacdoi:import-wikidata
        {--fetch : gọi Wikidata Query Service; bỏ qua để dùng lại file JSON thô đã lưu}';

    protected $description = 'Nhập danh mục món Việt từ Wikidata (CC0) → data/dishes/vn/ (DATA-005)';

    private const string RAW_PATH = 'data/reference/wikidata-vn-dishes.json';

    /**
     * Truy vấn SPARQL. Hai điểm bắt buộc, đã sai thật khi thử 2026-08-31:
     *  1. Q826059 mới là "ẩm thực Việt Nam" (không phải Q1968435).
     *  2. PHẢI lọc theo lớp món/thực phẩm — P2012 (cuisine) cũng gắn cho NHÀ HÀNG,
     *     P495 (country of origin) gắn cho mọi thứ xuất xứ VN.
     */
    private const string SPARQL = <<<'SPARQL'
SELECT DISTINCT ?dish ?dishLabel ?labelVi ?labelEn ?image ?cuisineLabel
       (GROUP_CONCAT(DISTINCT ?ingLabel; separator="|") AS ?ingredients)
WHERE {
  {
    ?cuisine (wdt:P279|wdt:P361)* wd:Q826059 .
    ?dish wdt:P2012 ?cuisine .
  } UNION {
    ?dish wdt:P495 wd:Q881 .
  }
  ?dish wdt:P31/wdt:P279* ?class .
  VALUES ?class { wd:Q746549 wd:Q2095 wd:Q19861951 }
  OPTIONAL { ?dish wdt:P18 ?image . }
  OPTIONAL { ?dish rdfs:label ?labelVi . FILTER(LANG(?labelVi) = "vi") }
  OPTIONAL { ?dish rdfs:label ?labelEn . FILTER(LANG(?labelEn) = "en") }
  OPTIONAL {
    ?dish (wdt:P527|wdt:P186) ?ing .
    ?ing rdfs:label ?ingLabel . FILTER(LANG(?ingLabel) = "en")
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "vi,en". }
}
GROUP BY ?dish ?dishLabel ?labelVi ?labelEn ?image ?cuisineLabel
ORDER BY ?dishLabel
SPARQL;

    public function handle(): int
    {
        $rawPath = base_path(self::RAW_PATH);

        if ($this->option('fetch')) {
            $this->info('Gọi Wikidata Query Service…');
            $payload = $this->fetch();
            $payload['_meta'] = [
                'queried_at' => Date::now()->toDateString(),
                'endpoint' => 'https://query.wikidata.org/sparql',
                'license' => 'CC0 1.0 (Wikidata)',
                'note' => 'Wikidata là dữ liệu SỐNG — chạy lại lần sau sẽ ra kết quả khác. Ngày truy vấn ở trên là mốc đối chiếu.',
                'sparql' => self::SPARQL,
            ];
            file_put_contents(
                $rawPath,
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"
            );
            $this->info('Đã lưu kết quả thô: '.self::RAW_PATH);
        }

        if (! is_file($rawPath)) {
            $this->error('Chưa có '.self::RAW_PATH.' — chạy lại với --fetch.');

            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($rawPath), true, 512, JSON_THROW_ON_ERROR);
        $rows = $payload['results']['bindings'] ?? [];
        $queriedAt = $payload['_meta']['queried_at'] ?? Date::now()->toDateString();

        $dir = base_path('data/dishes/vn');
        @mkdir($dir, 0777, true);

        $written = 0;
        $skipped = 0;
        $usedSlugs = [];

        foreach ($rows as $row) {
            $qid = basename($row['dish']['value'] ?? '');
            $nameVi = trim($row['labelVi']['value'] ?? '');
            $nameEn = trim($row['labelEn']['value'] ?? '');
            $display = $nameVi !== '' ? $nameVi : $nameEn;

            if ($display === '' || $display === $qid) {
                $skipped++;

                continue; // không có nhãn người đọc được — bỏ, không tạo món rác
            }

            $slug = Str::slug($display);
            if ($slug === '' || isset($usedSlugs[$slug])) {
                $skipped++;

                continue;
            }
            $usedSlugs[$slug] = true;

            $path = "{$dir}/{$slug}.json";
            if (is_file($path)) {
                $skipped++;

                continue; // đã có món (có thể do người biên soạn) — KHÔNG ghi đè
            }

            $names = [];
            if ($nameVi !== '') {
                $names[] = ['locale' => 'vi', 'name' => $nameVi, 'is_primary' => true];
            }
            if ($nameEn !== '' && $nameEn !== $nameVi) {
                $names[] = ['locale' => 'en', 'name' => $nameEn, 'is_primary' => true];
            }

            $ingredientHints = array_values(array_filter(explode('|', $row['ingredients']['value'] ?? '')));
            $image = $row['image']['value'] ?? null;

            $note = "Nhập tự động từ Wikidata {$qid} ngày {$queriedAt} (CC0). CHƯA QUA BIÊN SOẠN: "
                .'chưa có vùng miền, bữa phù hợp, cách chế biến, định lượng nguyên liệu. '
                .'Người biên soạn phải bổ sung rồi mới chuyển status=published.';
            if ($ingredientHints !== []) {
                $note .= ' Gợi ý nguyên liệu từ Wikidata (tiếng Anh, CHƯA ánh xạ sang data/ingredients/): '
                    .implode(', ', $ingredientHints).'.';
            }
            if ($image !== null) {
                $note .= " Ảnh nguồn (CHƯA tải về, phải kiểm license từng ảnh — R4/Q4): {$image}";
            }

            $payloadDish = [
                'slug' => $slug,
                'parent_dish_slug' => null,
                'country_code' => 'VN',
                'region_code' => null,
                'names' => $names,
                'is_eat_out' => false,
                'is_home_cook' => false,
                'cooking_method' => null,
                'completeness_level' => 1,
                'meal_slots' => [],
                'serving_desc' => null,
                'serving_grams' => null,
                'ingredients' => [],
                'recipe_steps' => null,
                'recipe_sources' => [],
                'media' => [],
                'estimated_kcal' => null,
                'external_ref' => ['source' => 'wikidata', 'id' => $qid, 'queried_at' => $queriedAt],
                'import_note' => $note,
                'status' => 'draft',
                'source' => 'imported',
                'confidence' => 'unknown',
                'needs_review' => true,
            ];

            $json = json_encode($payloadDish, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);
            $json = preg_replace_callback('/^ +/m', fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json);
            file_put_contents($path, $json."\n");
            $written++;
        }

        $this->updateManifest($rawPath, count($rows), $queriedAt);
        $this->info("Đã sinh {$written} file món trong data/dishes/vn/ (bỏ qua {$skipped}: thiếu nhãn, trùng slug, hoặc đã tồn tại).");
        $this->warn('Mọi món đều status=draft, needs_review=true — KHÔNG được hiển thị trước khi người biên soạn duyệt.');

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function fetch(): array
    {
        $url = 'https://query.wikidata.org/sparql?format=json&query='.urlencode(self::SPARQL);
        $ctx = stream_context_create(['http' => [
            'method' => 'GET',
            'header' => "User-Agent: LacDoi-DataImport/1.0 (du an bien soan du lieu mon an Viet Nam)\r\n"
                ."Accept: application/sparql-results+json\r\n",
            'timeout' => 180,
        ]]);

        try {
            $raw = file_get_contents($url, false, $ctx);
        } catch (Throwable $e) {
            throw new RuntimeException('Không gọi được Wikidata Query Service: '.$e->getMessage());
        }
        if ($raw === false) {
            throw new RuntimeException('Không gọi được Wikidata Query Service (kiểm tra mạng / rate limit).');
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    private function updateManifest(string $rawPath, int $rows, string $queriedAt): void
    {
        $manifestPath = base_path('data/reference/MANIFEST.json');
        $manifest = json_decode((string) file_get_contents($manifestPath), true) ?? [];
        $manifest['wikidata-vn-dishes.json'] = [
            'source_url' => 'https://query.wikidata.org/sparql',
            'source_release' => "Truy vấn SPARQL món Việt (cuisine Q826059 UNION country of origin Q881, lọc theo lớp món/thực phẩm), {$rows} kết quả",
            'downloaded_at' => $queriedAt,
            'original_sha256' => hash_file('sha256', $rawPath),
            'filtered_rows' => $rows,
            'license' => 'CC0 1.0 (Wikidata)',
            'filter_note' => 'Wikidata là dữ liệu SỐNG — kết quả thay đổi theo thời gian. Câu truy vấn nguyên văn lưu trong _meta.sparql của chính file JSON.',
        ];
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $json = preg_replace_callback('/^ +/m', fn (array $m) => str_repeat(' ', intdiv(strlen($m[0]), 2)), $json);
        file_put_contents($manifestPath, $json."\n");
    }
}
