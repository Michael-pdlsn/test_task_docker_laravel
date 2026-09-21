<?php

namespace Tests\Unit;

use App\Exceptions\InvalidDomainException;
use App\Services\WhoisService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WhoisServiceTest extends TestCase
{
    #[DataProvider('validDomains')]
    public function test_normalizes_valid_domains(string $input, string $expected): void
    {
        $this->assertSame($expected, (new WhoisService())->normalize($input));
    }

    public static function validDomains(): array
    {
        return [
            'simple' => ['example.com', 'example.com'],
            'upper case and spaces' => ['  Example.COM ', 'example.com'],
            'trailing dot' => ['example.com.', 'example.com'],
            'subdomain' => ['mail.example.co.uk', 'mail.example.co.uk'],
            'short' => ['t.co', 't.co'],
            'idn (cyrillic)' => ['приклад.укр', 'xn--80aikifvh.xn--j1amh'],
            'idn keeps sharp s (non-transitional)' => ['faß.de', 'xn--fa-hia.de'],
        ];
    }

    #[DataProvider('invalidDomains')]
    public function test_rejects_invalid_domains(string $input): void
    {
        $this->expectException(InvalidDomainException::class);

        (new WhoisService())->normalize($input);
    }

    public static function invalidDomains(): array
    {
        return [
            'no tld' => ['localhost'],
            'leading hyphen' => ['-example.com'],
            'empty label' => ['example..com'],
            'shell injection attempt' => ['example.com; rm -rf /'],
            'numeric tld' => ['example.123'],
            'label longer than 63' => [str_repeat('a', 64).'.com'],
            'longer than 253' => [str_repeat('a.', 127).'com'],
            'only dots' => ['...'],
        ];
    }
}
