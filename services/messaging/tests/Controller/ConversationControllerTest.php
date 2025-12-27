<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConversationControllerTest extends WebTestCase
{
    public function testHealthCheck(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals('messaging-service', $response['service']);
    }

    public function testCreateConversationRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/conversations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['listing_id' => 1]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListConversationsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/conversations');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetConversationRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/conversations/01234567-89ab-cdef-0123-456789abcdef');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testPostMessageRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/conversations/01234567-89ab-cdef-0123-456789abcdef/messages', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['content' => 'Hello']));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testMarkReadRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/conversations/01234567-89ab-cdef-0123-456789abcdef/read');

        $this->assertResponseStatusCodeSame(401);
    }
}

