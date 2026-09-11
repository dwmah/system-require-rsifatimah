<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemPreviewTest extends TestCase
{
    /**
     * Memastikan route /dev/design-system dapat diakses dengan sukses (HTTP 200) pada environment local / testing.
     */
    public function test_design_system_preview_renders_successfully_on_local(): void
    {
        $response = $this->get('/dev/design-system');

        $response->assertStatus(200);
        $response->assertSee('Design System');
        $response->assertSee('RSI Fatimah Banyuwangi');
        $response->assertSee('Primary Action');
        $response->assertSee('Lowongan Pekerjaan');
    }
}
