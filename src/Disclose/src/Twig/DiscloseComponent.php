<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Twig;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\UX\Disclose\Context\DiscloseContext;
use Symfony\UX\Disclose\Context\DiscloseContextFactory;
use Symfony\UX\Disclose\DiscloseUrlGenerator;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

/**
 * Renders the masked trigger view and the data display view of a protected
 * value. The value itself is never rendered: only the endpoint URL is exposed.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
#[AsTwigComponent('ux:disclose', template: '@Disclose/components/Disclose.html.twig')]
final class DiscloseComponent
{
    /**
     * A disclose context, or any object a subject resolver can resolve
     * (an entity, a document, or a plain value) to build the context from.
     *
     * @var DiscloseContext|object|null
     */
    public ?object $context = null;

    /**
     * Overrides the field to disclose.
     */
    public ?string $field = null;

    /**
     * Application-specific payload carried to the disclosure endpoint.
     *
     * @var array<string, mixed>
     */
    public array $payload = [];

    /**
     * Twig template rendered on the server when the value is disclosed, with
     * the resolved subject and "vars" as variables. Use it to reveal a whole
     * block of markup (for example a table of fields).
     */
    public ?string $render = null;

    /**
     * Name of a block of the "render" template to render at disclosure time,
     * so the revealed markup can live in the same template as the component
     * instead of a dedicated file. The block receives "subject" and "context".
     */
    public ?string $block = null;

    /**
     * Extra variables passed to the "render" template.
     *
     * @var array<string, mixed>
     */
    public array $vars = [];

    public string $mask = '••••••';

    /**
     * Native tooltip shown on hover for the reveal button. Null uses the
     * bundle translation.
     */
    public ?string $title = null;

    /**
     * @var string|null null uses the bundle translation
     */
    public ?string $revealLabel = null;

    public ?string $hideLabel = null;

    public ?string $loadingLabel = null;

    public ?string $errorLabel = null;

    public ?string $rateLimitedLabel = null;

    private ?string $url = null;

    private ?DiscloseUrlGenerator $urlGenerator = null;

    private ?DiscloseContextFactory $contextFactory = null;

    #[Required]
    public function setDiscloseUrlGenerator(DiscloseUrlGenerator $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    #[Required]
    public function setDiscloseContextFactory(DiscloseContextFactory $contextFactory): void
    {
        $this->contextFactory = $contextFactory;
    }

    #[PostMount]
    public function normalize(): void
    {
        if (null === $this->context) {
            throw new \LogicException('The "context" property of the "<twig:ux:disclose>" component is required. Pass a disclose context or a managed object.');
        }

        $context = $this->context instanceof DiscloseContext
            ? $this->context
            : $this->contextFactory->createFromObject($this->context, $this->field, $this->payload)
        ;

        if (null !== $this->field && null === $context->field) {
            $context = $context->withField($this->field);
        }

        $extra = $context->getExtra();
        if (null !== $this->render) {
            $extra['template'] = $this->render;
            $extra['block'] = $this->block;
            $extra['vars'] = $this->vars;
        }
        if ($this->payload) {
            $extra = array_merge($extra, $this->payload);
        }

        $this->url = $this->urlGenerator->generate($context->withExtra($extra));
    }

    public function getUrl(): string
    {
        return $this->url ?? '';
    }

    public function getRenderHtml(): bool
    {
        return null !== $this->render;
    }
}
