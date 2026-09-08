<?php

namespace App\Disclose;

use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\Disclose\RateLimiter\DiscloseRateLimitSubjectFactoryInterface;

/**
 * Keys the disclosure rate limit on a per-visitor reference passed as a
 * query parameter: every browser gets an isolated disclosure budget.
 *
 * Demo only: the reference comes from the request itself, so a visitor can
 * change it to reset their own budget. Do not reuse this pattern in
 * production.
 */
final class DemoSubjectFactory implements DiscloseRateLimitSubjectFactoryInterface
{
    public function create(Request $request): ?string
    {
        return $request->query->has('r') ? 'reference:'.$request->query->get('r') : null;
    }
}
