<?php

namespace App\Shared\Trait;

use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait AuthorableTrait
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }
}