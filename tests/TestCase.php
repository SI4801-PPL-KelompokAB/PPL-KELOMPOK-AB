<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan CSRF verification tidak memblokir HTTP POST/PUT/PATCH pada testing
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}

