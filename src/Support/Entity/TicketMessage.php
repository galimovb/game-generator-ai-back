<?php

namespace App\Support\Entity;

use App\Shared\Enum\TicketMessageType;
use App\Shared\Trait\AuthorableTrait;
use App\Shared\Trait\TimestampableTrait;
use App\Support\Repository\TicketMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketMessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TicketMessage
{
    use TimestampableTrait;
    use AuthorableTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?array $photos = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticket = null;

    #[ORM\Column(enumType: TicketMessageType::class)]
    private ?TicketMessageType $messageType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): static
    {
        $this->photos = $photos;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getMessageType(): ?TicketMessageType
    {
        return $this->messageType;
    }

    public function setMessageType(TicketMessageType $messageType): static
    {
        $this->messageType = $messageType;

        return $this;
    }
}
