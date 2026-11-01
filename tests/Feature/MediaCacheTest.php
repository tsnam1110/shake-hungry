<?php

declare(strict_types=1);

/* Cache ảnh (2026-11-01): /media có ETag + max-age 7 ngày + must-revalidate,
   hỏi lại khi chưa đổi phải trả 304 rỗng thay vì tải lại thân ảnh. */

it('ảnh media: header cache đúng và If-None-Match trả 304', function () {
    $file = glob(base_path('data/media/dishes/*.webp'))[0] ?? null;
    expect($file)->not->toBeNull();
    $url = '/media/dishes/'.basename($file);

    $resp = $this->get($url);
    $resp->assertOk();
    expect($resp->headers->get('Cache-Control'))->toContain('max-age=604800')
        ->and($resp->headers->get('Cache-Control'))->toContain('must-revalidate')
        ->and($resp->headers->get('ETag'))->not->toBeNull();

    $this->call('GET', $url, [], [], [], ['HTTP_IF_NONE_MATCH' => $resp->headers->get('ETag')])
        ->assertStatus(304);
});
