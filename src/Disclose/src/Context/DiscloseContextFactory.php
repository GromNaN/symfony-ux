<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Context;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Builds a disclose context, from scratch or out of an object, at render time.
 *
 * The object path only works when a Doctrine manager knows the class: entities,
 * ODM documents, or any class managed by a registered object manager.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 *
 * @internal
 */
final class DiscloseContextFactory
{
    public function __construct(
        private readonly ?ManagerRegistry $doctrineRegistry = null,
        private readonly ?ManagerRegistry $doctrineMongodbRegistry = null,
    ) {
    }

    public function create(string $class, string|int|array $id, ?string $field = null, array $extra = []): DiscloseContext
    {
        return DiscloseContext::create($class, $id, $field, $extra);
    }

    public function createFromObject(object $subject, ?string $field = null, array $extra = []): DiscloseContext
    {
        foreach ([$this->doctrineRegistry, $this->doctrineMongodbRegistry] as $registry) {
            if (null === $registry) {
                continue;
            }

            $objectManager = $registry->getManagerForClass($subject::class);
            if (null === $objectManager) {
                continue;
            }

            $ids = (array) $objectManager->getClassMetadata($subject::class)->getIdentifierValues($subject);
            $id = 1 === \count($ids) ? reset($ids) : $ids;

            return DiscloseContext::create($subject::class, $id, $field, $extra);
        }

        throw new \LogicException(\sprintf('Cannot build a disclose context from the object "%s". Pass an explicit context or register a subject resolver data source.', $subject::class));
    }
}
