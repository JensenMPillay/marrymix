<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::ARRAY, nullable: true)]
    private ?array $carousel = [];

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::FLOAT, nullable: true)]
    private ?float $price = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER, nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    private ?\App\Entity\Category $category = null;

    #[ORM\ManyToMany(targetEntity: Bundle::class, inversedBy: 'products')]
    private \Doctrine\Common\Collections\Collection|array $bundle;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItem;

    public function __construct()
    {
        $this->bundle = new ArrayCollection();
        $this->orderItem = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCarousel(): ?array
    {
        return $this->carousel;
    }

    public function setCarousel(?array $carousel): self
    {
        $this->carousel = $carousel;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Bundle>
     */
    public function getBundle(): Collection
    {
        return $this->bundle;
    }

    public function addBundle(Bundle $bundle): self
    {
        if (!$this->bundle->contains($bundle)) {
            $this->bundle[] = $bundle;
        }

        return $this;
    }

    public function removeBundle(Bundle $bundle): self
    {
        $this->bundle->removeElement($bundle);

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItem(): Collection
    {
        return $this->orderItem;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItem->contains($orderItem)) {
            $this->orderItem->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItem->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }
}
