<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Disclose\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\UX\Disclose\Context\DiscloseContext;
use Symfony\UX\Disclose\Tests\Fixtures\CollectingTestLogger;
use Symfony\UX\Disclose\Tests\Fixtures\Subject\FixtureData;

/**
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
final class DiscloseControllerTest extends WebTestCase
{
    public function testDisclosesTheValueToAnAuthorizedUser(): void
    {
        $client = $this->authenticatedClient();
        $client->request('GET', $this->discloseUrl());

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-Type'));
        self::assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        self::assertStringContainsString('"value":"the-secret"', $response->getContent());
    }

    public function testRendersAServerTemplateWhenConfigured(): void
    {
        $client = $this->authenticatedClient();
        $context = DiscloseContext::create(FixtureData::class, '42', 'secret', [
            'template' => 'disclose_render.html.twig',
            'vars' => ['greeting' => 'Hello'],
        ]);
        $client->request('GET', static::getContainer()->get('ux.disclose.url_generator')->generate($context));

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertStringContainsString('<p>Hello the-secret</p>', $data['html']);
    }

    public function testDeniesAnonymousUsers(): void
    {
        $client = static::createClient();
        $client->request('GET', $this->discloseUrl());

        $response = $client->getResponse();
        self::assertSame(403, $response->getStatusCode());
        self::assertStringContainsString('"code":"DISCLOSE_DENIED"', $response->getContent());
    }

    public function testRateLimitsRepeatedDisclosures(): void
    {
        $client = $this->authenticatedClient();

        $client->request('GET', $this->discloseUrl());
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('GET', $this->discloseUrl());
        self::assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('GET', $this->discloseUrl());
        $response = $client->getResponse();
        self::assertSame(429, $response->getStatusCode());
        self::assertStringContainsString('"code":"DISCLOSE_RATE_LIMITED"', $response->getContent());
        self::assertStringContainsString('"retry_after":', $response->getContent());
        self::assertNotNull($response->headers->get('Retry-After'));
    }

    public function testRejectsATamperedContext(): void
    {
        $client = static::createClient();
        $url = $this->discloseUrl();

        // Flipping the signature must invalidate the whole request.
        $client->request('GET', $url.'&h=AAAA');

        $response = $client->getResponse();
        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('"code":"DISCLOSE_INVALID"', $response->getContent());
    }

    public function testReturnsNotFoundWhenTheSubjectIsMissing(): void
    {
        $client = static::createClient();
        $client->request('GET', $this->discloseUrlForClass(FixtureData::class, '999'));

        $response = $client->getResponse();
        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('"code":"DISCLOSE_SUBJECT_NOT_FOUND"', $response->getContent());
    }

    public function testReturnsServerErrorWhenTheClassIsNotManaged(): void
    {
        $client = static::createClient();
        $client->catchExceptions(true);
        $client->request('GET', $this->discloseUrlForClass('App\DoesNotExist'));

        self::assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function testWritesTheAuditTrailOnSuccess(): void
    {
        $client = $this->authenticatedClient();
        $client->request('GET', $this->discloseUrl());

        /** @var CollectingTestLogger $logger */
        $logger = static::getContainer()->get('test.logger');
        $successRecords = array_values(array_filter(
            $logger->getRecords(),
            static fn (array $record): bool => str_contains($record['message'], 'Protected value disclosure: success'),
        ));

        self::assertCount(1, $successRecords);
        self::assertSame(FixtureData::class, $successRecords[0]['context']['context']['class']);
        self::assertStringStartsWith('test-', $successRecords[0]['context']['identity']);
    }

    private function authenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->setServerParameter('PHP_AUTH_USER', 'mr_discloser');
        $client->setServerParameter('PHP_AUTH_PW', 'symfonypass');
        // Isolated rate-limit budget for this test.
        $client->setServerParameter('UX_DISCLOSE_SUBJECT', 'test-'.bin2hex(random_bytes(4)));

        return $client;
    }

    private function discloseUrl(): string
    {
        return $this->discloseUrlForClass(FixtureData::class);
    }

    private function discloseUrlForClass(string $class, string $id = '42'): string
    {
        return static::getContainer()
            ->get('ux.disclose.url_generator')
            ->generate(DiscloseContext::create($class, $id, 'secret'))
        ;
    }
}
