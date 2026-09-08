<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\RateLimiter;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Consumes a token for the current request against the rate limiter.
 *
 * The limiter is keyed per authenticated user and falls back to the client IP
 * for anonymous requests, unless the application provides a custom subject
 * factory.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 *
 * @internal
 */
final class DiscloseRateLimiter
{
    public function __construct(
        private readonly ?Security $security = null,
        private readonly ?RateLimiterFactory $rateLimiterFactory = null,
        private readonly ?DiscloseRateLimitSubjectFactoryInterface $subjectFactory = null,
    ) {
    }

    /**
     * Consumes one token. Returns null when the rate limiter is disabled.
     */
    public function consume(Request $request): ?RateLimit
    {
        if (null === $this->rateLimiterFactory) {
            return null;
        }

        return $this->rateLimiterFactory->create($this->resolveSubject($request))->consume();
    }

    /**
     * Returns the identity the current request is accounted against.
     */
    public function identity(Request $request): string
    {
        return $this->resolveSubject($request);
    }

    private function resolveSubject(Request $request): string
    {
        if (null !== $this->subjectFactory && null !== $subject = $this->subjectFactory->create($request)) {
            return $subject;
        }

        $user = $this->security?->getUser();

        if ($user instanceof UserInterface) {
            return 'user:'.$user->getUserIdentifier();
        }

        return 'ip:'.$request->getClientIp();
    }
}
