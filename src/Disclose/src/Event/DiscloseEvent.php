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
 * (for example a dedicated audit storage).
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class DiscloseEvent extends Event
{
    public const ATTEMPT = 'disclose.attempt';
    public const SUCCESS = 'disclose.success';
    public const REJECTED = 'disclose.rejected';

    public function __construct(
        public readonly DiscloseContext $context,
        public readonly ?object $subject = null,
        public readonly ?string $value = null,
        public readonly ?DiscloseStatus $status = null,
    ) {
    }
}
