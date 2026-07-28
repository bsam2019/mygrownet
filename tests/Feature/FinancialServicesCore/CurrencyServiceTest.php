<?php

namespace Tests\Feature\FinancialServicesCore;

use App\Domain\FinancialServicesCore\Contracts\CurrencyService;
use App\Domain\FinancialServicesCore\Entities\Currency;
use App\Domain\FinancialServicesCore\Entities\ExchangeRate;
use App\Domain\FinancialServicesCore\Repositories\CurrencyRepositoryInterface;
use App\Domain\FinancialServicesCore\Repositories\ExchangeRateRepositoryInterface;
use App\Domain\FinancialServicesCore\Services\CurrencyServiceImpl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CurrencyService $currencyService;
    private CurrencyRepositoryInterface $currencyRepo;
    private ExchangeRateRepositoryInterface $exchangeRateRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currencyRepo = $this->app->make(CurrencyRepositoryInterface::class);
        $this->exchangeRateRepo = $this->app->make(ExchangeRateRepositoryInterface::class);

        $this->currencyService = new CurrencyServiceImpl(
            $this->currencyRepo,
            $this->exchangeRateRepo,
        );
    }

    private function seedRates(): void
    {
        $now = new \DateTimeImmutable();
        $this->exchangeRateRepo->save(ExchangeRate::create('ZMW', 'USD', 0.055, $now, 'test'));
        $this->exchangeRateRepo->save(ExchangeRate::create('USD', 'ZMW', 18.18, $now, 'test'));
        $this->exchangeRateRepo->save(ExchangeRate::create('ZMW', 'ZAR', 0.93, $now, 'test'));
    }

    public function test_identity_conversion_returns_same_amount(): void
    {
        $result = $this->currencyService->convert(250, 'ZMW', 'ZMW');
        $this->assertEquals(250, $result);
    }

    public function test_convert_currency_with_rate(): void
    {
        $this->seedRates();

        $result = $this->currencyService->convert(100, 'ZMW', 'USD');
        $this->assertEquals(5.5, $result);
    }

    public function test_get_rate_returns_valid_rate(): void
    {
        $this->seedRates();

        $rate = $this->currencyService->getRate('ZMW', 'USD');
        $this->assertEquals(0.055, $rate);
    }

    public function test_get_rate_throws_for_unsupported_currency(): void
    {
        $this->expectException(\App\Domain\FinancialServicesCore\Exceptions\FinancialServicesCoreException::class);
        $this->currencyService->getRate('ZMW', 'XYZ');
    }

    public function test_supported_currencies_returns_all_active(): void
    {
        $currencies = $this->currencyService->supportedCurrencies();

        $codes = array_map(fn($c) => $c['code'], $currencies);
        $this->assertContains('ZMW', $codes);
        $this->assertContains('USD', $codes);
        $this->assertCount(5, $currencies);
    }

    public function test_exception_when_converting_disabled_currency(): void
    {
        $this->seedRates();

        $zmw = $this->currencyRepo->findByCode('ZMW');
        $zmw->disable();
        $this->currencyRepo->save($zmw);

        $this->expectException(\App\Domain\FinancialServicesCore\Exceptions\FinancialServicesCoreException::class);
        $this->currencyService->convert(100, 'ZMW', 'USD');
    }
}
