<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\RedirectBundle\Tests\Functional\Controller;

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class RedirectRouteControllerTest extends SuluTestCase
{
    const BASE_URL = '/api/redirect-routes';

    /**
     * @var array
     */
    private $defaultData;

    /**
     * @var array
     */
    private $status410Data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDatabase();

        $this->defaultData = ['source' => '/test1', 'target' => '/test2', 'enabled' => true, 'statusCode' => 301];
        $this->status410Data = ['source' => '/test410', 'enabled' => true, 'statusCode' => 410, 'target' => null];
    }

    public function testPost()
    {
        $response = $this->post($this->defaultData);

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        foreach ($this->defaultData as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPost410()
    {
        $response = $this->post($this->status410Data);

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        foreach ($this->status410Data as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPostAlreadyExists()
    {
        $response = $this->post($this->defaultData);
        $this->assertHttpStatusCode(200, $response);

        $response = $this->post($this->defaultData);
        $this->assertHttpStatusCode(409, $response);
    }

    public function testGet()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $response = $this->get($data['id']);

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        foreach ($this->defaultData as $key => $value) {
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testPutSameData()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);
        $this->assertHttpStatusCode(200, $response);

        $response = $this->put($data['id'], $this->defaultData);
        $this->assertHttpStatusCode(200, $response);
    }

    public function testPutAlreadyExists()
    {
        $response = $this->post($this->defaultData);
        $this->assertHttpStatusCode(200, $response);

        $response = $this->post($this->defaultData);
        $this->assertHttpStatusCode(409, $response);
    }

    public function testPut()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $newData = ['source' => '/test3', 'target' => '/test4', 'enabled' => false, 'statusCode' => 302];
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

        $client = $this->createAuthenticatedClient();
        $client->request('GET', self::BASE_URL);
        $response = $client->getResponse();

        $this->assertHttpStatusCode(200, $response);
        $result = json_decode($response->getContent(), true);

        $this->assertCount(1, $result['_embedded']['redirect_routes']);
        $this->assertEquals($data['id'], $result['_embedded']['redirect_routes'][0]['id']);
    }

    public function testDelete()
    {
        $response = $this->post($this->defaultData);
        $data = json_decode($response->getContent(), true);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', self::BASE_URL . '/' . $data['id']);
        $this->assertHttpStatusCode(204, $client->getResponse());
    }

    public function testDeleteNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', self::BASE_URL . '/123-123-123');
        $this->assertHttpStatusCode(404, $client->getResponse());
    }

    public function testCDelete()
    {
        $response = $this->post($this->defaultData);
        $data1 = json_decode($response->getContent(), true);

        $response = $this->post(['source' => '/test2', 'target' => '/test3', 'enabled' => true, 'statusCode' => 301]);
        $data2 = json_decode($response->getContent(), true);

        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', self::BASE_URL . '?ids=' . $data1['id'] . ',' . $data2['id']);
        $this->assertHttpStatusCode(204, $client->getResponse());
    }

    private function post($data)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', self::BASE_URL, $data);

        return $client->getResponse();
    }

    private function get($id)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', self::BASE_URL . '/' . $id);

        return $client->getResponse();
    }

    private function put($id, $data)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', self::BASE_URL . '/' . $id, $data);

        return $client->getResponse();
    }
}
