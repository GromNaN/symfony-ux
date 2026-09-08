<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Audit;

use Psr\Log\LogLevel;

/**
 * Outcome of a disclosure request, used for the audit trail.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
enum DiscloseStatus: string
{
    case Attempt = 'attempt';
    case Success = 'success';
    case AuthDenied = 'auth_denied';
    case RateLimited = 'rate_limited';

    /**
     * PSR-3 level name for the audit record.
     */
    public function logLevel(): string
    {
        return match ($this) {
            self::Attempt, self::Success => LogLevel::INFO,
            self::AuthDenied, self::RateLimited => LogLevel::WARNING,
        };
    }
}
