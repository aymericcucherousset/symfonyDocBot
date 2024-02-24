<?php

namespace App\Entity;

use App\Repository\EmbeddingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use LLPhant\Embeddings\VectorStores\Doctrine\DoctrineEmbeddingEntityBase;

#[ORM\Entity(repositoryClass: EmbeddingRepository::class)]
#[ORM\Table(name: 'embedding')]
class Embedding extends DoctrineEmbeddingEntityBase
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public ?string $type;

    #[ORM\Column(type: Types::STRING, nullable: true, length: 5)]
    public ?string $version;
}
