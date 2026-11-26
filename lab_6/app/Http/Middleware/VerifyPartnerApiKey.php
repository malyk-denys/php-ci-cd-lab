<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyPartnerApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = (string) $request->header('X-Partner-Api-Key', '');
        $expectedKey = (string) config('services.ticket_partner.api_key', '');

        if ($expectedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return new JsonResponse([
                'status' => 'access_denied',
                'message' => 'Відхилено: відсутній або недійсний заголовок X-Partner-Api-Key.',
            ], 403);
        }

        $response = $next($request);
        $response->headers->set('X-Security-Policy', 'Partner-Key-Enforced');

        return $response;
    }
}
