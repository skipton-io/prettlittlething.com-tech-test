<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 * @ORM\Table(indexes={@ORM\Index(name="log_id", columns={"log_id"}), @ORM\Index(name="sku", columns={"sku"})})
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $normal_price;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $special_price;

    /**
     * @ORM\Column(type="guid", nullable=true)
     */
    private $logId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNormalPrice(): ?float
    {
        return $this->normal_price;
    }

    public function setNormalPrice(float $normal_price): self
    {
        $this->normal_price = $normal_price;

        return $this;
    }

    public function getSpecialPrice(): ?float
    {
        return $this->special_price;
    }

    public function setSpecialPrice(?float $special_price): self
    {
        $this->special_price = $special_price;

        return $this;
    }

    public function getLogId(): ?string
    {
        return $this->logId;
    }

    public function setLogId(?string $logId): self
    {
        $this->logId = $logId;

        return $this;
    }
}
