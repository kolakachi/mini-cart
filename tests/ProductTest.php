<?php
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testFetchProducts()
    {
        $client = new Client(['base_uri' => 'http://localhost:8080']);
        $response = $client->request('GET', '/products');
        $this->assertEquals(200, $response->getStatusCode());
    }
}