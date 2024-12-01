<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductValue::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $productValue;

    public function __construct()
    {
        $this->productValue = new ArrayCollection();
    }

    public function getProductValue(): Collection
    {
        return $this->productValue;
    }

    public function addProductValue(ProductValue $productValue): self
    {
        if (!$this->productValue->contains($productValue)) {
            $productValue->setProduct($this);
            $this->productValue[] = $productValue;
        }
        return $this;
    }

    public function removeProductValue(ProductValue $productValue): self
    {
        if ($this->productValue->contains($productValue)) {
            $this->productValue->removeElement($productValue);
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
