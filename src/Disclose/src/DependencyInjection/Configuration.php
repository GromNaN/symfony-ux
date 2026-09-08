<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('disclose');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('rate_limiter')
                    ->info('Names of the framework rate limiters combined for every disclosure. A request is accepted only when every limiter accepts it (for example a burst window plus a daily quota).')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(static fn (string $value): array => [$value])
                    ->end()
                    ->defaultValue(['ux_disclose'])
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('rate_limiter_subject_factory')
                    ->info('Service id computing the rate-limit subject, to key the limiter differently than user-then-IP.')
                    ->defaultNull()
                ->end()
                ->scalarNode('logger')
                    ->info('PSR-3 logger service id used for the audit trail.')
                    ->defaultValue('logger')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
