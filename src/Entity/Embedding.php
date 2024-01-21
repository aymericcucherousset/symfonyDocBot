<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineEmbeddingEntityBase;

#[ORM\Entity()]
#[ORM\Table(name: 'embedding')]
class Embedding extends DoctrineEmbeddingEntityBase
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public ?string $type;
}
