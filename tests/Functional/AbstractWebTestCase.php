<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    public const HTTP_GET_METHOD = 'GET';
    public const HTML_SELECTOR_SUBMIT_BUTTON = 'button[type="submit"]';
}
