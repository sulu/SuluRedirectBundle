<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class RedirectRouteControllerTest extends SuluTestCase
{
    public const BASE_URL = '/admin/api/redirect-routes';

    /**
     * @var array{
     *      source: string,
     *      sourceHost: string|null,
     *      target: string|null,
     *      statusCode: int,
     *  }
     */
    private $defaultData;

    /**
     * @var array{
     *      source: string,
     *      sourceHost: string|null,
     *      target: string|null,
     *      statusCode: int,
     *  }
     */
    private $status410Data;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createAuthenticatedClient();
        $this->purgeDatabase();

        $this->defaultData = ['source' => '/test1', 'sourceHost' => null, 'target' => '/test2', 'statusCode' => 301];
        $this->status410Data = ['source' => '/test410', 'sourceHost' => null, 'target' => null, 'statusCode' => 410];
    }

    public function testPost()
    {
        $response = $this->post($this->defaultData);

        $this->assertHttpStatusCode(200, $response);
        /** @var array<string, mixed> $result */
        $result = json_decode($response->getContent(), true);

        foreach ($this->defaultData as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPost410()
    {
        $response = $this->post($this->status410Data);

        $this->assertHttpStatusCode(200, $response);
        /** @var array<string, mixed> $result */
        $result = json_decode($response->getContent(), true);

        foreach ($this->status410Data as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPostAlreadyExists()
    {
        $response = $this->post($this->defaultData);
        $this->assertHttpStatusCode(200, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => '/test1']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => 'test1']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => '/TEST1']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => 'TEST1']));
        $this->assertHttpStatusCode(409, $response);
    }

    public function testPostWithSourceHostAlreadyExists()
    {
        $response = $this->post(array_merge($this->defaultData, ['source' => '/test1', 'sourceHost' => 'sulu.io']));
        $this->assertHttpStatusCode(200, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => '/test1', 'sourceHost' => 'sulu.io']));
        $this->assertHttpStatusCode(409, $response);
    }

    public function testPostTriggerActionEnable(): void
    {
        $response = $this->post(array_merge($this->defaultData, ['enabled' => false]));
        /** @var array<string, mixed> $data */
        $data = json_decode($response->getContent(), true);

        $this->client->request('POST', self::BASE_URL . '/' . $data['id'], [
            'action' => 'enable',
        ]);
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(200, $response);

        /** @var array<string, mixed> $content */
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
    }

    public function testPostTriggerActionDisable(): void
    {
        $response = $this->post(array_merge($this->defaultData, ['enabled' => true]));
        /** @var array<string, mixed> $data */
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $data);

        $this->client->request('POST', self::BASE_URL . '/' . $data['id'], [
            'action' => 'disable',
        ]);
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(200, $response);

        /** @var array<string, mixed> $content */
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['enabled']);
    }

    public function testGet()
    {
        $response = $this->post($this->defaultData);
        /** @var array<string, mixed> $data */
        $data = json_decode($response->getContent(), true);

        $response = $this->get($data['id']);

        $this->assertHttpStatusCode(200, $response);
        /** @var array<string, mixed> $result */
        $result = json_decode($response->getContent(), true);

        foreach ($this->defaultData as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPutSameData()
    {
        $response = $this->post($this->defaultData);
        /** @var array<string, mixed> $data */
        $data = json_decode($response->getContent(), true);
        $this->assertHttpStatusCode(200, $response);
        $this->assertArrayHasKey('id', $data);

        $response = $this->put($data['id'], $this->defaultData);
        $this->assertHttpStatusCode(200, $response);
    }

    public function testPutAlreadyExists()
    {
        $response = $this->post($this->defaultData);
        /** @var array<string, mixed> $data */
        $data = json_decode($response->getContent(), true);
        $this->assertHttpStatusCode(200, $response);

        $response = $this->post(array_merge($this->defaultData, ['source' => '/test2']));
        $this->assertHttpStatusCode(200, $response);
        $this->assertArrayHasKey('id', $data);

        $response = $this->put($data['id'], array_merge($this->defaultData, ['source' => '/test2']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->put($data['id'], array_merge($this->defaultData, ['source' => 'test2']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->put($data['id'], array_merge($this->defaultData, ['source' => '/TEST2']));
        $this->assertHttpStatusCode(409, $response);

        $response = $this->put($data['id'], array_merge($this->defaultData, ['source' => 'TEST2']));
        $this->assertHttpStatusCode(409, $response);
    }

    public function testPut()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $newData = ['source' => '/test3', 'sourceHost' => null, 'target' => '/test4', 'statusCode' => 302];
        $response = $this->put($data['id'], $newData);

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        foreach ($newData as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testCGet()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $this->client->request('GET', self::BASE_URL);
        $response = $this->client->getResponse();

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        $this->assertCount(1, $result['_embedded']['redirect_routes']);
        $this->assertEquals($data['id'], $result['_embedded']['redirect_routes'][0]['id']);
    }

    public function testDelete()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $this->client->request('DELETE', self::BASE_URL . '/' . $data['id']);
        $this->assertHttpStatusCode(204, $this->client->getResponse());
    }

    public function testDeleteNotExisting()
    {
        $this->client->request('DELETE', self::BASE_URL . '/123-123-123');
        $this->assertHttpStatusCode(404, $this->client->getResponse());
    }

    public function testCDelete()
    {
        $response = $this->post($this->defaultData);
        $data1 = json_decode($response->getContent(), true);

        $response = $this->post(['source' => '/test2', 'sourceHost' => null, 'target' => '/test3', 'statusCode' => 301]);
        $data2 = json_decode($response->getContent(), true);

        $this->client->request('DELETE', self::BASE_URL . '?ids=' . $data1['id'] . ',' . $data2['id']);
        $this->assertHttpStatusCode(204, $this->client->getResponse());
    }

    private function post($data)
    {
        $this->client->request('POST', self::BASE_URL, $data);

        return $this->client->getResponse();
    }

    private function get($id)
    {
        $this->client->request('GET', self::BASE_URL . '/' . $id);

        return $this->client->getResponse();
    }

    private function put($id, $data)
    {
        $this->client->request('PUT', self::BASE_URL . '/' . $id, $data);

        return $this->client->getResponse();
    }
}
