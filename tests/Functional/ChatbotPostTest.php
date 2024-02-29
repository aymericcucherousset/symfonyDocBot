<?php

namespace App\Tests\Functional;

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
    }
}
