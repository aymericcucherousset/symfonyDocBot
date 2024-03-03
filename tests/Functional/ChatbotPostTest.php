<?php

namespace App\Tests\Functional;

use App\Service\ChatManager;

class ChatbotPostTest extends AbstractWebTestCase
{
    public const HTML_SELECTOR_QUESTION_INPUT = 'input[name="chatbot[question]"]';
    public const HTML_SELECTOR_VERSION_INPUT = 'select[name="chatbot[version]"]';

    public function testFormIsAvailable(): void
    {
        $client = static::createClient();
        $client->request(self::HTTP_GET_METHOD, '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);

        $this->assertSelectorNotExists('h2');
    }

    public function testSubmitEmptyForm(): void
    {
        $client = static::createClient();
        $client->request(self::HTTP_GET_METHOD, '/');

        $client->submitForm('Poser la question', []);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorTextContains('li', 'Veuillez remplir ce champ svp.');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);

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

        $client->request(self::HTTP_GET_METHOD, '/');

        // Submit the form
        $client->submitForm('Poser la question', [
            'chatbot[question]' => 'How to create a controller?',
            'chatbot[version]' => '5.4',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);
        $this->assertSelectorTextContains('h2', 'Réponse');
        $this->assertSelectorTextContains('p', 'To create a controller, you need to');
    }
}
