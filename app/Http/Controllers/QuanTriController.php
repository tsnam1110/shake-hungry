<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Quản trị tối thiểu cho 3 module chưa có trang admin React (hoàn thiện mục 5b).
 *
 * LỰA CHỌN PHƯƠNG ÁN (ghi rõ theo yêu cầu hạng mục): dùng trang Blade thay vì
 * thêm 3 trang React/antd, vì (1) React cần thêm controller API + page + build
 * antd cho 3 module trong một đợt đã nặng; (2) quan trọng hơn, `lacdoi:export-data`
 * hiện KHÔNG phủ combinations/vegetables/drinks — sửa DB qua admin rồi chạy
 * migrate:fresh --seed sẽ MẤT sửa đổi (file data/ mới là nguồn sự thật, QT-3).
 * Vì vậy trang này sửa TRỰC TIẾP file JSON trong data/ (đã validate), và
 * git diff chính là vết kiểm toán. Sau khi lưu, người vận hành chạy
 * `php artisan migrate:fresh --seed` + `lacdoi:validate-data` để áp dụng —
 * KHÔNG tự động migrate từ web (migrate:fresh xóa cả bảng người dùng).
 *
 * Phạm vi sửa: chỉ các trường ghi chú/cờ an toàn, KHÔNG sửa định danh
 * (side_a/side_b, slug, công thức, tháng mùa vụ) — đổi định danh là tạo bản
 * ghi mới, phải làm qua file + duyệt như quy trình biên soạn thường.
 */
class QuanTriController extends Controller
{
    private const array MODULES = [
        'ket-hop' => ['dir' => 'data/combinations', 'label' => 'Kết hợp cần lưu ý', 'edit' => 'quantri.comb-edit'],
        'rau-mua' => ['dir' => 'data/vegetables', 'label' => 'Rau theo mùa', 'edit' => 'quantri.veg-edit'],
        'do-uong' => ['dir' => 'data/drinks', 'label' => 'Nước uống', 'edit' => 'quantri.drink-edit'],
    ];

    public function index()
    {
        $counts = [];
        foreach (self::MODULES as $key => $m) {
            $counts[$key] = count(glob(base_path($m['dir'].'/*.json')) ?: []);
        }

        return view('quantri.index', ['modules' => self::MODULES, 'counts' => $counts]);
    }

    public function list(Request $request, string $module)
    {
        $m = self::MODULES[$module] ?? abort(404);
        $q = trim((string) $request->query('q', ''));
        $rows = [];
        foreach (glob(base_path($m['dir'].'/*.json')) ?: [] as $file) {
            $j = json_decode((string) File::get($file), true);
            if (! is_array($j)) {
                continue;
            }
            $title = $j['name_vi'] ?? $j['slug'] ?? basename($file);
            if ($q !== '' && ! str_contains(mb_strtolower($title.' '.($j['slug'] ?? '')), mb_strtolower($q))) {
                continue;
            }
            $rows[] = ['slug' => $j['slug'] ?? basename($file, '.json'), 'title' => $title, 'needs_review' => $j['needs_review'] ?? null, 'uncertain' => $j['uncertain'] ?? null];
        }
        usort($rows, fn ($a, $b) => strcmp($a['title'], $b['title']));

        return view('quantri.list', ['module' => $module, 'label' => $m['label'], 'rows' => $rows, 'q' => $q]);
    }

    public function edit(string $module, string $slug)
    {
        $m = self::MODULES[$module] ?? abort(404);
        $data = $this->read($m['dir'], $slug);

        return view($m['edit'], ['module' => $module, 'label' => $m['label'], 'slug' => $slug, 'data' => $data]);
    }

    public function update(Request $request, string $module, string $slug)
    {
        $m = self::MODULES[$module] ?? abort(404);
        $data = $this->read($m['dir'], $slug);

        $fields = match ($module) {
            'ket-hop' => $request->validate([
                'condition_vi' => ['nullable', 'string', 'max:2000'],
                'mechanism_vi' => ['nullable', 'string', 'max:2000'],
                'science_status' => ['required', 'string', 'in:supported,conditional,unproven,refuted'],
                'needs_review' => ['nullable', 'boolean'],
            ]),
            'rau-mua' => $request->validate([
                'selection_note' => ['nullable', 'string', 'max:2000'],
                'storage_note' => ['nullable', 'string', 'max:2000'],
                'cooking_note' => ['nullable', 'string', 'max:2000'],
                'safety_note' => ['nullable', 'string', 'max:2000'],
                'uncertain_note' => ['nullable', 'string', 'max:2000'],
                'uncertain' => ['nullable', 'boolean'],
                'needs_review' => ['nullable', 'boolean'],
            ]),
            'do-uong' => $request->validate([
                'cultural_note' => ['nullable', 'string', 'max:2000'],
                'sugar_note' => ['nullable', 'string', 'max:2000'],
                'alcohol_note' => ['nullable', 'string', 'max:2000'],
                'label_note' => ['nullable', 'string', 'max:2000'],
                'uncertain_note' => ['nullable', 'string', 'max:2000'],
                'caffeine_level' => ['nullable', 'string', 'in:khong,thap,trung-binh,cao'],
                'uncertain' => ['nullable', 'boolean'],
                'needs_review' => ['nullable', 'boolean'],
            ]),
            default => abort(404),
        };

        foreach ($fields as $k => $v) {
            if ($k === 'needs_review' || $k === 'uncertain') {
                $data[$k] = (bool) $v;
            } elseif ($v === null || $v === '') {
                $data[$k] = null;
            } else {
                $data[$k] = $v;
            }
        }
        // Checkbox không gửi khi bỏ chọn: mặc định false cho hai cờ.
        foreach (['needs_review', 'uncertain'] as $flag) {
            if (! array_key_exists($flag, $fields) && array_key_exists($flag, $data)) {
                $data[$flag] = false;
            }
        }

        $this->write($m['dir'], $slug, $data);

        return redirect()->route('quantri.list', ['module' => $module])
            ->with('status', "Đã lưu {$m['label']} / {$slug} vào {$m['dir']}/{$slug}.json — chạy `php artisan migrate:fresh --seed` rồi `lacdoi:validate-data` để áp dụng lên trang công khai.");
    }

    /** @return array<string, mixed> */
    private function read(string $dir, string $slug): array
    {
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
        File::put($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
    }
}
