<?php

namespace App\Entity;

use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: PropertyRepository::class)]
class Property
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "NONE")]
    private UuidInterface $propertyId;

    #[ORM\Column(length: 255)]
    private ?string $propertyType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $propertyDescription = null;

    #[ORM\Column]
    private ?int $propertyPrice = null;

    #[ORM\Column(length: 255)]
    private ?string $propertyLocation = null;

    #[ORM\Column(nullable: true)]
    private ?int $propertyAge = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datePosted = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: false)]
    private ?User $propertyOwner = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $propertyOwnerName = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $thumbnail = null;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'property', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    public function __construct()
    {
        $this->propertyId = Uuid::uuid4();
        $this->images = new ArrayCollection();
    }

    public function getPropertyId(): UuidInterface
    {
        return $this->propertyId;
    }
    public function getPropertyType(): ?string
    {
        return $this->propertyType;
    }

    public function setPropertyType(string $propertyType): static
    {
        $this->propertyType = $propertyType;
        return $this;
    }

    public function getPropertyDescription(): ?string
    {
        return $this->propertyDescription;
    }

    public function setPropertyDescription(?string $propertyDescription): static
    {
        $this->propertyDescription = $propertyDescription;
        return $this;
    }

    public function getPropertyPrice(): ?int
    {
        return $this->propertyPrice;
    }

    public function setPropertyPrice(int $propertyPrice): static
    {
        $this->propertyPrice = $propertyPrice;
        return $this;
    }

    public function getPropertyLocation(): ?string
    {
        return $this->propertyLocation;
    }

    public function setPropertyLocation(string $propertyLocation): static
    {
        $this->propertyLocation = $propertyLocation;
        return $this;
    }

    public function getPropertyAge(): ?int
    {
        return $this->propertyAge;
    }

    public function setPropertyAge(?int $propertyAge): static
    {
        $this->propertyAge = $propertyAge;
        return $this;
    }

    public function getDatePosted(): ?\DateTimeInterface
    {
        return $this->datePosted;
    }

    public function setDatePosted(\DateTimeInterface $datePosted): static
    {
        $this->datePosted = $datePosted;
        return $this;
    }

    public function getPropertyOwner(): ?User
    {
        return $this->propertyOwner;
    }

    public function setPropertyOwner(User $propertyOwner): static
    {
        $this->propertyOwner = $propertyOwner;
        return $this;
    }
    public function getPropertyOwnerName(): ?string
    {
        return $this->propertyOwnerName;
    }

    public function setPropertyOwnerName(string $propertyOwnerName): static
    {
        $this->propertyOwnerName = $propertyOwnerName;
        return $this;
    }

    public function removePropertyOwner(): static
    {
        $this->propertyOwner = null;
        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProperty($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProperty() === $this) {
                $image->setProperty(null);
            }
        }

        return $this;
    }
}
