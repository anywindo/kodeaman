<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAs($user, $guard = null)
    {
        \Laravel\Sanctum\Sanctum::actingAs($user, ['*']);
        return parent::actingAs($user, $guard);
    }
}
