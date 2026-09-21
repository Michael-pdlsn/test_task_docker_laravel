<?php

namespace App\Services;

use App\Exceptions\InvalidDomainException;
use App\Exceptions\WhoisLookupException;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;

class WhoisService
{
    /** A whois server may not answer at all — never let a request hang on it. */
    private const TIMEOUT_SECONDS = 10;

    private const MAX_DOMAIN_LENGTH = 253;

    /**
     * Exit codes of the whois client that still carry a real answer:
     * 0 — record found, 1 — "no match" (the registry reply is printed).
     * 2 and above are client errors (DNS, network), even if some output was printed.
     */
    private const ANSWER_EXIT_CODES = [0, 1];

    public function lookup(string $domain): string
    {
        $domain = $this->normalize($domain);

        try {
            // Array form: arguments go to the binary directly, no shell is involved.
            $result = Process::timeout(self::TIMEOUT_SECONDS)->run(['whois', $domain]);
        } catch (ProcessException $e) {
            // Timeout, failure to start, killed by a signal.
            throw new WhoisLookupException("Whois lookup for {$domain} failed: {$e->getMessage()}", previous: $e);
        }

        $output = trim($result->output());
        if (!in_array($result->exitCode(), self::ANSWER_EXIT_CODES, true) || $output === '') {
            throw new WhoisLookupException(sprintf(
                'Whois lookup for %s failed with exit code %s: %s',
                $domain,
                var_export($result->exitCode(), true),
                trim($result->errorOutput()),
            ));
        }

        return $output;
    }

    /**
     * Lower-cases the domain, drops a trailing dot and converts internationalized
     * names (e.g. "приклад.укр") to punycode, then validates the result.
     * Non-transitional IDNA2008 processing keeps "ß" as is, as registries do (not "ss").
     */
    public function normalize(string $domain): string
    {
        $domain = rtrim(mb_strtolower(trim($domain)), '.');

        if (preg_match('/[^\x00-\x7F]/', $domain)) {
            $ascii = idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
            if ($ascii === false) {
                throw new InvalidDomainException('Некоректне доменне ім\'я.');
            }
            $domain = $ascii;
        }

        if ($domain === '' || strlen($domain) > self::MAX_DOMAIN_LENGTH) {
            throw new InvalidDomainException('Довжина доменного імені повинна бути від 1 до 253 символів.');
        }

        // Labels of 1–63 chars without leading/trailing hyphens; the TLD is letters or punycode.
        $label = '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?';
        if (!preg_match("/^(?:{$label}\\.)+(?:[a-z]{2,63}|xn--[a-z0-9-]{1,59})$/", $domain)) {
            throw new InvalidDomainException('Некоректне доменне ім\'я.');
        }

        return $domain;
    }
}
