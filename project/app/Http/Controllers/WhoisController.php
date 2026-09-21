<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidDomainException;
use App\Exceptions\WhoisLookupException;
use App\Services\WhoisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WhoisController extends Controller
{
    public function lookup(Request $request, WhoisService $service): JsonResponse
    {
        $domain = $request->input('domain');
        if (!is_string($domain) || trim($domain) === '') {
            return $this->error('Вкажіть доменне ім\'я.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            return response()->json(['data' => $service->lookup($domain)]);
        } catch (InvalidDomainException $e) {
            return $this->error($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (WhoisLookupException $e) {
            // Details go to the log; the user gets a message without internals.
            report($e);

            return $this->error('Не вдалося отримати дані WHOIS. Спробуйте пізніше.', Response::HTTP_BAD_GATEWAY);
        }
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }
}
