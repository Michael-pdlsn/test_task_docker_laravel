<?php

namespace App\Services;

class WhoisService
{
    public function lookup(string $domain): string
    {
        if (mb_strlen($domain) < 4 || mb_strlen($domain) > 253) {
            throw new \InvalidArgumentException('Довжина доменного імені повинна бути від 4 до 253 символів.');
        }

        if (!preg_match('/^(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', $domain)) {
            throw new \InvalidArgumentException('Некорректне доменне ім\'я.');
        }

        $output = shell_exec('whois ' . escapeshellarg($domain));

        if (empty($output)) {
            throw new \RuntimeException('Whois command failed or returned no output.');
        }

        return $output;
    }
}

