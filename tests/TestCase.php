<?php

namespace Tests;

use App\Services\JwtService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsApi($user)
    {
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);
        $this->withHeader('Authorization', 'Bearer '.$token);

        return $this;
    }
}
