<?php

declare(strict_types=1);

it('trang chu tra ve 200', function () {
    $this->get('/')->assertStatus(200);
});
