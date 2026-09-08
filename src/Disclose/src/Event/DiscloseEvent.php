<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\UX\Disclose\Audit\DiscloseStatus;
use Symfony\UX\Disclose\Context\DiscloseContext;

/**
 * Dispatched around a disclosure so applications can plug their own logic
 * (for example a dedicated audit storage). The event name identifies the
 * step (attempt, success, auth denied or rate limited).
 *
 * The event never carries the disclosed value: listeners must not be able to
 * observe the sensitive data.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class DiscloseEvent extends Event
{
    public const ATTEMPT = 'disclose.attempt';
    public const SUCCESS = 'disclose.success';
    public const AUTH_DENIED = 'disclose.auth_denied';
    public const RATE_LIMITED = 'disclose.rate_limited';

    public function __construct(
        public readonly DiscloseContext $context,
        public readonly ?object $subject = null,
        public readonly DiscloseStatus $status,
    ) {}
}
