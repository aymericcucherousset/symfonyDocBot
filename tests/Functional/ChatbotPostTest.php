<?php

namespace App\Tests\Functional;

use App\Service\ChatManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChatbotPostTest extends WebTestCase
{
    public function testFormIsAvailable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="chatbot[question]"]');
        $this->assertSelectorExists('select[name="chatbot[version]"]');
        $this->assertSelectorExists('button[type="submit"]');

        $this->assertSelectorNotExists('h2');
    }

    public function testSubmitEmptyForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $client->submitForm('Poser la question', []);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorTextContains('li', 'Veuillez remplir ce champ svp.');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="chatbot[question]"]');
        $this->assertSelectorExists('select[name="chatbot[version]"]');
        $this->assertSelectorExists('button[type="submit"]');

        $this->assertSelectorNotExists('h2');
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $chatManagerMock = $this->createMock(ChatManager::class);
        $chatManagerMock->method('generateAnswer')
            ->willReturn('To create a controller, you need to')
        ;

        $client->getContainer()->set(ChatManager::class, $chatManagerMock);

        $client->request('GET', '/');

        // Submit the form
        $client->submitForm('Poser la question', [
            'chatbot[question]' => 'How to create a controller?',
            'chatbot[version]' => '5.4',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="chatbot[question]"]');
        $this->assertSelectorExists('select[name="chatbot[version]"]');
        $this->assertSelectorExists('button[type="submit"]');
        $this->assertSelectorTextContains('h2', 'Réponse');
        $this->assertSelectorTextContains('p', 'To create a controller, you need to');
    }
}
