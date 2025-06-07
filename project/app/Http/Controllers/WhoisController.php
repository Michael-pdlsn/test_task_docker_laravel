<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhoisService;

class WhoisController extends Controller
{
    public function lookup(Request $request, WhoisService $service)
    {
        try {
            $request->validate([
                'domain' => [
                    'required',
                    'string',
                ]
            ]);
            $domain = $request->input('domain');
            $output = $service->lookup($domain);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => $output]);
    }
}
