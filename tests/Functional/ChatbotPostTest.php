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

    public function testFormIsAvailableInEnglish(): void
    {
        $client = static::createClient();
        $client->request(self::HTTP_GET_METHOD, '/en');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains(self::HTML_SELECTOR_SUBMIT_BUTTON, 'Ask the question');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);

        $this->assertSelectorNotExists('h2');
    }

    public function testFormIsAvailableInFrench(): void
    {
        $client = static::createClient();
        $client->request(self::HTTP_GET_METHOD, '/fr');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains(self::HTML_SELECTOR_SUBMIT_BUTTON, 'Poser la question');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);

        $this->assertSelectorNotExists('h2');
    }

    public function testSubmitEmptyForm(): void
    {
        $client = static::createClient();
        $client->request(self::HTTP_GET_METHOD, '/');

        $client->submitForm('Ask the question', []);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorTextContains('form > div > ul > li', 'Please fill in this field.');
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
        $client->submitForm('Ask the question', [
            'chatbot[question]' => 'How to create a controller?',
            'chatbot[version]' => '5.4',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymfonyDocBot');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists(self::HTML_SELECTOR_QUESTION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_VERSION_INPUT);
        $this->assertSelectorExists(self::HTML_SELECTOR_SUBMIT_BUTTON);
        $this->assertSelectorTextContains('h2', 'Response:');
        $this->assertSelectorTextContains('p', 'To create a controller, you need to');
    }
}
