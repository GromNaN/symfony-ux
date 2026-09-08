<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Tests\Fixtures\Discloser;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\Disclose\Context\DiscloseContext;
use Symfony\UX\Disclose\DiscloserInterface;
use Symfony\UX\Disclose\Tests\Fixtures\Subject\FixtureData;

/**
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class DummyDiscloser implements DiscloserInterface
{
    public function __construct(private readonly ?Security $security = null)
    {
    }

    public function supports(object $subject): bool
    {
        return $subject instanceof FixtureData;
    }

    public function isGranted(object $subject): bool
    {
        return null !== $this->security?->getUser();
    }

    public function disclose(object $subject, DiscloseContext $context): string
    {
        return $subject->secret;
    }
}
