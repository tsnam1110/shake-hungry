<?php

declare(strict_types=1);

namespace App\Http\Controllers\PublicWeb;

use App\Support\Thumbnail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * IMG-001: phục vụ file ảnh trong data/media/ (nguồn sự thật là file trong
 * repo theo QT-3, không chép sang public/). Chỉ nhận đúng hai nhóm
 * dishes/ingredients và file .webp đã qua kiểm license (R4).
 *
 * Chính sách cache (chủ dự án chốt 2026-11-01): CHỈ ảnh được cache — trang
 * và tài nguyên khác không cache dài hạn để người dùng luôn nhận cập nhật
 * ngay mà không cần biết xoá cache. Ảnh ít thay đổi nên cache 7 ngày, kèm
 * ETag + must-revalidate: quá hạn trình duyệt hỏi lại máy chủ, ảnh chưa đổi
 * trả 304 (không tải lại thân ảnh), ảnh đã thay file thì nhận bản mới.
 */
class MediaController
{
    public function show(Request $request, string $path): BinaryFileResponse
    {
        abort_unless(preg_match('#^(dishes|ingredients)/[a-z0-9][a-z0-9\-]*\.webp$#', $path) === 1, 404);
        $file = base_path('data/media/'.$path);
        abort_unless(is_file($file), 404);

        return $this->fileResponse($request, $file, $file);
    }

    /**
     * Thumbnail cho thẻ danh sách (quy tắc 2026-11-03, xem Thumbnail):
     * chỉ nhận chiều rộng trong Thumbnail::WIDTHS và đúng khuôn ảnh gốc.
     * Cache trình duyệt giống ảnh gốc (7 ngày + ETag + 304), nhưng ETag
     * gắn thêm chiều rộng và dấu thời gian ảnh gốc để ảnh gốc đổi thì
     * thumbnail không kẹt bản cũ. Sinh thumbnail lỗi thì phục vụ thẳng
     * ảnh gốc, không trả ảnh vỡ.
     */
    public function thumb(Request $request, int $width, string $path): BinaryFileResponse
    {
        abort_unless(in_array($width, Thumbnail::WIDTHS, true), 404);
        abort_unless(preg_match('#^(dishes|ingredients)/[a-z0-9][a-z0-9\-]*\.webp$#', $path) === 1, 404);
        $original = base_path('data/media/'.$path);
        abort_unless(is_file($original), 404);

        $file = Thumbnail::cachedFile($path, $width) ?? $original;

        return $this->fileResponse($request, $file, $original.'|thumb-'.$width);
    }

    private function fileResponse(Request $request, string $file, string $etagSource): BinaryFileResponse
    {
        $response = response()->file($file, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=604800, must-revalidate',
        ]);
        $response->setETag(md5($etagSource.'|'.filemtime($file).'|'.filesize($file)));
        $modified = \DateTimeImmutable::createFromFormat('U', (string) filemtime($file));
        if ($modified !== false) {
            $response->setLastModified($modified);
        }
        if ($response->isNotModified($request)) {
            return $response;
        }

        return $response;
    }
}
