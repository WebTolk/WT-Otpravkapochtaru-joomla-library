<?php

namespace Webtolk\Tests\Unit\Dictionaries;

use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Dictionaries\CountryDictionary;

final class CountryDictionaryTest extends TestCase
{
    public function testAllReturnsKnownRussianPostCountryEntry(): void
    {
        $countryByCode = array_column(CountryDictionary::all(), null, 'code');

        self::assertSame('Российская федерация', $countryByCode[643]['name'] ?? null);
        self::assertSame('Russian federation', $countryByCode[643]['nameEn'] ?? null);
    }
}
