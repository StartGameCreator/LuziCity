<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_documentation_discovery_openapi_and_guide_are_public(): void
    {
        $this->getJson('/api/v1/docs')->assertOk()
            ->assertJsonPath('name', 'LuziCity Public API')
            ->assertJsonPath('version', '1.0.0');

        $specification = $this->get('/api/v1/docs/openapi.yaml')->assertOk()
            ->assertHeader('content-type', 'application/yaml; charset=UTF-8')
            ->assertSee('openapi: 3.1.0')->getContent();
        foreach (['/news:', '/categories:', '/videos:', '/podcasts:', '/events:', '/auth/tokens:', '/mobile/feed:', '/mobile/favorites:', '/mobile/profile:', 'bearerAuth:', 'PaginatedResponse:', 'ValidationError:'] as $contract) {
            $this->assertStringContainsString($contract, $specification);
        }

        $this->get('/api/v1/docs/guide.md')->assertOk()
            ->assertHeader('content-type', 'text/markdown; charset=UTF-8')
            ->assertSee('## Paginação')->assertSee('## Erros')->assertSee('curl');
    }
}
