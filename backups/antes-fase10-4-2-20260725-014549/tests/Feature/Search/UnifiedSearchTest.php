<?php

namespace Tests\Feature\Search;

use Tests\TestCase;

class UnifiedSearchTest extends TestCase
{
    public function test_search_page_is_public(): void
    {
        $this->get('/buscar')->assertOk()->assertSee('Busca inteligente');
    }

    public function test_search_accepts_query_and_type(): void
    {
        $this->get('/buscar?q=luzi&type=all')->assertOk()->assertSee('luzi');
    }

    public function test_suggestions_validate_minimum_length(): void
    {
        $this->getJson('/buscar/sugestoes?q=a')->assertUnprocessable();
    }
}
