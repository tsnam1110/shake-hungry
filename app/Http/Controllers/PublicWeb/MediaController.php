<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * IMG-001: phục vụ file ảnh trong data/media/ (nguồn sự thật là file trong
 * repo theo QT-3, không chép sang public/). Chỉ nhận đúng hai nhóm
 * dishes/ingredients và file .webp đã qua kiểm license (R4).
 */
class MediaController
{
    public function show(Request $request, string $path): BinaryFileResponse
    {
        abort_unless(preg_match('#^(dishes|ingredients)/[a-z0-9][a-z0-9\-]*\.webp$#', $path) === 1, 404);
        $file = base_path('data/media/'.$path);
        abort_unless(is_file($file), 404);

        return response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
