<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PerPageTest extends TestCase
{
    public function test_page_size_defaults_to_15_and_stays_between_1_and_50(): void
    {
        $this->assertSame(15, perPage(Request::create('/tasks')));
        $this->assertSame(20, perPage(Request::create('/tasks?per_page=20')));
        $this->assertSame(50, perPage(Request::create('/tasks?per_page=1000')));
        $this->assertSame(1, perPage(Request::create('/tasks?per_page=-3')));
    }
}
