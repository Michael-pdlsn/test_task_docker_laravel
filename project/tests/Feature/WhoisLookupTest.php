<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Tests\TestCase;

class WhoisLookupTest extends TestCase
{
    public function test_main_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('whois-form', false);
    }

    public function test_returns_whois_output(): void
    {
        Process::fake(['*' => Process::result(output: "Domain Name: EXAMPLE.COM\n")]);

        $this->postJson(route('whois.lookup'), ['domain' => 'Example.com'])
            ->assertOk()
            ->assertExactJson(['data' => 'Domain Name: EXAMPLE.COM']);

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['whois', 'example.com']);
    }

    public function test_converts_idn_before_lookup(): void
    {
        Process::fake(['*' => Process::result(output: 'ok')]);

        $this->postJson(route('whois.lookup'), ['domain' => 'приклад.укр'])->assertOk();

        Process::assertRan(fn (PendingProcess $process) => $process->command === ['whois', 'xn--80aikifvh.xn--j1amh']);
    }

    public function test_requires_domain(): void
    {
        Process::fake();

        $this->postJson(route('whois.lookup'), [])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);

        Process::assertNothingRan();
    }

    public function test_rejects_invalid_domain_without_running_whois(): void
    {
        Process::fake();

        $this->postJson(route('whois.lookup'), ['domain' => 'example.com; rm -rf /'])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);

        Process::assertNothingRan();
    }

    public function test_empty_whois_output_is_a_bad_gateway(): void
    {
        Process::fake(['*' => Process::result(output: '', errorOutput: 'connect: Network is unreachable', exitCode: 1)]);

        $response = $this->postJson(route('whois.lookup'), ['domain' => 'example.com'])
            ->assertStatus(502)
            ->assertJsonStructure(['error']);

        // Internal details must not leak to the client.
        $this->assertStringNotContainsString('unreachable', $response->json('error'));
    }

    public function test_client_error_with_partial_output_is_a_bad_gateway(): void
    {
        // e.g. the registry answered, then the registrar's server could not be reached.
        Process::fake(['*' => Process::result(output: 'Domain Name: EXAMPLE.COM', errorOutput: 'getaddrinfo: Name or service not known', exitCode: 2)]);

        $this->postJson(route('whois.lookup'), ['domain' => 'example.com'])->assertStatus(502);
    }

    public function test_process_exception_is_a_bad_gateway_in_json(): void
    {
        Process::fake(['*' => fn () => throw new ProcessRuntimeException('Unable to launch a new process.')]);

        $this->postJson(route('whois.lookup'), ['domain' => 'example.com'])
            ->assertStatus(502)
            ->assertJsonStructure(['error']);
    }

    public function test_not_found_answer_with_non_zero_exit_code_is_returned(): void
    {
        Process::fake(['*' => Process::result(output: 'No match for "EXAMPLE-NOT-REGISTERED.COM".', exitCode: 1)]);

        $this->postJson(route('whois.lookup'), ['domain' => 'example-not-registered.com'])
            ->assertOk()
            ->assertJsonPath('data', 'No match for "EXAMPLE-NOT-REGISTERED.COM".');
    }
}
