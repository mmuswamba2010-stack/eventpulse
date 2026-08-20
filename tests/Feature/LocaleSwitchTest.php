<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_peut_changer_la_langue_en_anglais(): void
    {
        $this->from('/login')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect('/login');

        $this->assertSame('en', session('locale'));
    }

    public function test_une_locale_invalide_renvoie_404(): void
    {
        $this->get('/locale/de')->assertNotFound();
    }
}
