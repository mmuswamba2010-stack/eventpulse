<?php

namespace Tests\Unit;

use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_auto_shows_usd_for_dollar_stored_amounts(): void
    {
        config(['eventpulse.catalog.price_currency' => 'auto']);
        config(['eventpulse.usd.enabled' => true, 'eventpulse.usd.cdf_per_usd' => 2250]);

        $this->assertSame('$ 95', Money::formatCatalog(95, false));
    }

    public function test_catalog_auto_shows_rounded_usd_for_cdf_stored_amounts(): void
    {
        config(['eventpulse.catalog.price_currency' => 'auto']);
        config(['eventpulse.usd.enabled' => true, 'eventpulse.usd.cdf_per_usd' => 2250]);

        $this->assertSame('$ 7', Money::formatCatalog(15000, false));
    }

    public function test_catalog_usd_mode_shows_whole_dollars_only(): void
    {
        config(['eventpulse.catalog.price_currency' => 'usd']);
        config(['eventpulse.usd.enabled' => true, 'eventpulse.usd.cdf_per_usd' => 2250]);

        $this->assertSame('$ 5', Money::formatCatalog(11250, false));
    }

    public function test_catalog_cdf_mode_shows_local_currency_only(): void
    {
        config(['eventpulse.catalog.price_currency' => 'cdf']);

        $this->assertSame('15 000 FC', Money::formatCatalog(15000, false));
    }

    public function test_event_price_shows_whole_usd_only(): void
    {
        config(['eventpulse.usd.enabled' => true, 'eventpulse.usd.cdf_per_usd' => 2250]);

        $this->assertSame('$ 180', Money::formatEventPrice(180, false));
        $this->assertSame('$ 7', Money::formatEventPrice(15000, false));
        $this->assertSame('$ 15', Money::formatEventPrice(33750, false));
        $this->assertSame('Gratuit', Money::formatEventPrice(0, true));
    }

    public function test_event_price_label_uses_dollars_word(): void
    {
        config(['eventpulse.usd.enabled' => true, 'eventpulse.usd.cdf_per_usd' => 2250]);

        $this->assertSame('80 dollars', Money::formatEventPriceLabel(80, false));
        $this->assertSame('Gratuit', Money::formatEventPriceLabel(0, true));
    }
}
