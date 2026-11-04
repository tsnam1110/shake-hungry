<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * API quản trị cho 3 module chưa có CRUD DB trong admin React
 * (kết hợp cần lưu ý, rau theo mùa, đồ uống) — Giai đoạn 1 của kế hoạch
 * thay thế /quan-tri bằng /admin, đã được Benjamin duyệt 2026-11-04.
 *
 * Khác với CRUD nguyên liệu/món/quả (sửa trực tiếp DB rồi export):
 * 3 module này lấy file trong data/ làm nguồn sự thật (QT-3), vì
 * `lacdoi:export-data` không phủ chúng. API này vì vậy đọc/ghi TRỰC
 * TIẾP file JSON, giống /quan-tri trước đây, và git diff chính là vết
 * kiểm toán (không ghi AdminAuditLog giả vào model DB khi DB chưa đổi;
 * DB chỉ đổi khi người vận hành chạy `lacdoi:reload-modules`).
 *
 * Phạm vi sửa: CHỈ các trường ghi chú/cờ an toàn dưới đây, y hệt
 * /quan-tri. Mọi khóa khác trong file (slug, định danh, công thức,
 * tháng mùa vụ, media, ...) không bao giờ bị ghi đè — kể cả khi client
 * gửi kèm, chúng cũng bị loại ở tầng validate.
 *
 * Ngữ nghĩa cập nhật: PUT này là cập nhật từng phần trên tập trường
 * cho phép — trường vắng mặt thì giữ nguyên (khác form Blade cũ, nơi
 * checkbox không gửi khi bỏ chọn nên bị ép false). Giá trị null/rỗng
 * thì xóa trường đó (đặt null), đúng quy tắc "không có thông tin thì
 * bỏ trống, không bịa".
 *
 * Trang công khai chỉ đổi sau khi chạy `lacdoi:reload-modules` — API
 * không tự nạp lại DB từ web, đúng tính chất an toàn của /quan-tri cũ
 * (không bao giờ chạy migrate:fresh từ web vì nó xóa bảng người dùng).
 */
class ModuleFileController extends Controller
{
    private const array MODULES = [
        'ket-hop' => ['dir' => 'data/combinations', 'label' => 'Kết hợp cần lưu ý'],
        'rau-mua' => ['dir' => 'data/vegetables', 'label' => 'Rau theo mùa'],
        'do-uong' => ['dir' => 'data/drinks', 'label' => 'Nước uống'],
    ];

    /** Tập trường cho phép sửa, mirror QuanTriController (trừ science_status thành nullable để cập nhật từng phần). */
    private const array FIELDS = [
        'ket-hop' => [
            'condition_vi' => ['nullable', 'string', 'max:2000'],
            'mechanism_vi' => ['nullable', 'string', 'max:2000'],
            'science_status' => ['nullable', 'string', 'in:supported,conditional,unproven,refuted'],
            'needs_review' => ['nullable', 'boolean'],
        ],
        'rau-mua' => [
            'selection_note' => ['nullable', 'string', 'max:2000'],
            'storage_note' => ['nullable', 'string', 'max:2000'],
            'cooking_note' => ['nullable', 'string', 'max:2000'],
            'safety_note' => ['nullable', 'string', 'max:2000'],
            'uncertain_note' => ['nullable', 'string', 'max:2000'],
            'uncertain' => ['nullable', 'boolean'],
            'needs_review' => ['nullable', 'boolean'],
        ],
        'do-uong' => [
            'cultural_note' => ['nullable', 'string', 'max:2000'],
            'sugar_note' => ['nullable', 'string', 'max:2000'],
            'alcohol_note' => ['nullable', 'string', 'max:2000'],
            'label_note' => ['nullable', 'string', 'max:2000'],
            'uncertain_note' => ['nullable', 'string', 'max:2000'],
            'caffeine_level' => ['nullable', 'string', 'in:khong,thap,trung-binh,cao'],
            'uncertain' => ['nullable', 'boolean'],
            'needs_review' => ['nullable', 'boolean'],
        ],
    ];

    public function index(Request $request, string $module): JsonResponse
    {
        $m = self::MODULES[$module] ?? abort(404);
        $q = trim((string) $request->query('q', ''));
        $rows = [];
        foreach (glob(base_path($m['dir'].'/*.json')) ?: [] as $file) {
            $j = json_decode((string) File::get($file), true);
            if (! is_array($j)) {
                continue;
            }
            $title = $j['name_vi'] ?? $j['slug'] ?? basename($file, '.json');
            if ($q !== '' && ! str_contains(mb_strtolower($title.' '.($j['slug'] ?? '')), mb_strtolower($q))) {
                continue;
            }
            $rows[] = [
                'slug' => $j['slug'] ?? basename($file, '.json'),
                'title' => $title,
                'needs_review' => $j['needs_review'] ?? null,
                'uncertain' => $j['uncertain'] ?? null,
            ];
        }
        usort($rows, fn ($a, $b) => strcmp($a['title'], $b['title']));

        return response()->json(['module' => $module, 'label' => $m['label'], 'total' => count($rows), 'data' => $rows]);
    }

    /** @return array<string, mixed> */
    public function show(string $module, string $slug): JsonResponse
    {
        $m = self::MODULES[$module] ?? abort(404);

        return response()->json($this->read($m['dir'], $slug));
    }

    public function update(Request $request, string $module, string $slug): JsonResponse
    {
        $m = self::MODULES[$module] ?? abort(404);
        $data = $this->read($m['dir'], $slug);
        $fields = $request->validate(self::FIELDS[$module]);

        foreach ($fields as $k => $v) {
            if ($k === 'needs_review' || $k === 'uncertain') {
                $data[$k] = (bool) $v;
            } elseif ($v === null || $v === '') {
                $data[$k] = null;
            } else {
                $data[$k] = $v;
            }
        }
        $this->write($m['dir'], $slug, $data);

        return response()->json([
            'data' => $data,
            'reload_hint' => 'Đã lưu file. Trang công khai chỉ đổi sau khi chạy `php artisan lacdoi:reload-modules '.$module.'` rồi `php artisan lacdoi:validate-data`.',
        ]);
    }

    /** @return array<string, mixed> */
    private function read(string $dir, string $slug): array
    {
        // Chặn path traversal: slug chỉ được chứa chữ, số, gạch dưới, gạch ngang.
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            abort(404);
        }
        $path = base_path($dir.'/'.$slug.'.json');
        if (! File::exists($path)) {
            abort(404);
        }
        $data = json_decode((string) File::get($path), true);
        if (! is_array($data)) {
            abort(500, 'File JSON không đọc được.');
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function write(string $dir, string $slug, array $data): void
    {
        $path = base_path($dir.'/'.$slug.'.json');
        $original = (string) File::get($path);
        // Giữ nguyên thụt lề của file gốc (file đồ uống dùng 2 khoảng trắng
        // do ghi bằng Python; json_encode của PHP mặc định 4) để git diff
        // chỉ hiện đúng dòng vừa sửa, không định dạng lại cả file.
        $indent = 4;
        foreach (explode("\n", $original) as $line) {
            if (preg_match('/^( +)\S/', $line, $m)) {
                $indent = strlen($m[1]) === 2 ? 2 : 4;
                break;
            }
        }
        $json = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($indent === 2) {
            // Chuỗi JSON đã escape ký tự điều khiển nên mỗi lần xuống dòng
            // đều là ranh giới cấu trúc — thay nhóm 4 khoảng trắng đầu dòng
            // thành 2 là an toàn.
            $json = (string) preg_replace_callback('/^( +)/m', fn ($m) => str_repeat(' ', (int) (strlen($m[1]) / 2)), $json);
        }
        File::put($path, $json."\n");
    }
}
