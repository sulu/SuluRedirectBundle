<?php


namespace Sulu\Bundle\RedirectBundle\Tests\Functional\Routing;

use Ramsey\Uuid\Uuid;
use Sulu\Bundle\RedirectBundle\Entity\RedirectRoute;
use Sulu\Bundle\TestBundle\Testing\WebsiteTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectRouteProviderTest extends WebsiteTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createWebsiteClient();
        static::purgeDatabase();
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testRoute(
        string $requestUrl,
        string $source,
        int $statusCode,
        ?string $target = null,
        ?string $sourceHost = null
    ) {
        // setup models
        $redirectRoute = new RedirectRoute();
        $redirectRoute->setId(Uuid::uuid4()->toString());
        $redirectRoute->setSource($source);
        $redirectRoute->setSourceHost($sourceHost);
        $redirectRoute->setStatusCode($statusCode);
        $redirectRoute->setTarget($target);
        $redirectRoute->setEnabled(true);

        // save models
        static::getEntityManager()->persist($redirectRoute);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->client->request('GET', $requestUrl);

        $response = $this->client->getResponse();
        $this->assertHttpStatusCode($statusCode, $response);

        if ($target) {
            $this->assertInstanceOf(RedirectResponse::class, $response);
            $this->assertSame($target, $response->getTargetUrl());
        }
    }

    public function routeDataProvider()
    {
        yield [
            '/test-301',
            '/test-301',
            301,
            '/test2'
        ];

        yield [
            '/test-302',
            '/test-302',
            302,
            '/test2'
        ];

        yield [
            '/test-401',
            '/test-401',
            410
        ];

        yield [
            'http://with-domain.com/test-domain-redirect',
            '/test-domain-redirect',
            301,
            '/',
            'with-domain.com'
        ];

        yield [
            '/test-emoticon-%F0%9F%8E%89', // browsers will encode the url and be provided this way to symfony getPathInfo
            '/test-emoticon-🎉',
            301,
            '/'
        ];
    }
}
