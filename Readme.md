# Symfony Doc Bot

Symfony (6.4) chat bot based on [Yoandev article](https://yoandev.co/construire-un-rag-en-php-avec-la-doc-de-symfony-llphant-et-openai) and [Symfony doc](https://github.com/symfony/symfony-docs).

## Requirement

- php: 8.3
- OpenAI API key

## Features

- Ask Symfony doc question
- Answer code display in md format
- Multiples Symfony versions
- Auto download Symfony doc (.rst files)

## Starting

1. Config your OpenAI API key in [.env](.env)
2. Install dependencies `composer install`
3. Run the project `symfony serve`

## Contribute

1. Run phpstan `vendor/bin/phpstan`
2. Run phpcsfixer `vendor/bin/php-cs-fixer fix`
3. Create Pull Request

## Contact

<contact@aymeric-cucherousset.fr>