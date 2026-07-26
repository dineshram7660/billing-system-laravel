<?php

namespace Tests\Unit;

use App\Support\IndianCurrency;
use PHPUnit\Framework\TestCase;

class IndianCurrencyTest extends TestCase
{
    public function test_it_converts_zero(): void
    {
        $this->assertSame('Zero Rupees Only', IndianCurrency::words(0));
    }

    public function test_it_converts_a_simple_amount(): void
    {
        $this->assertSame('Two Hundred Thirty Six Rupees Only', IndianCurrency::words(236));
    }

    public function test_it_converts_amounts_with_teens(): void
    {
        $this->assertSame('Nineteen Rupees Only', IndianCurrency::words(19));
        $this->assertSame('One Hundred Fifteen Rupees Only', IndianCurrency::words(115));
    }

    public function test_it_handles_the_indian_lakh_crore_grouping(): void
    {
        $this->assertSame(
            'One Lakh Twenty Three Thousand Four Hundred Fifty Six Rupees Only',
            IndianCurrency::words(123456)
        );
        $this->assertSame(
            'One Crore Rupees Only',
            IndianCurrency::words(10000000)
        );
    }

    public function test_it_skips_zero_groups(): void
    {
        $this->assertSame('One Thousand Rupees Only', IndianCurrency::words(1000));
    }
}
