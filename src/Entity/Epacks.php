<?php

namespace App\Entity;

use App\Repository\EpacksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EpacksRepository::class)
 */
class Epacks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $updater;

    /**
    * @ORM\Column(type="text")
    */
    private $manifest;

    /**
    * @ORM\Column(type="text")
    */
    private $structure;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mpn;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ean;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $brandname;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $productname;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUpdated;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMpn(): ?string
    {
        return $this->mpn;
    }

    public function setMpn($mpn): self
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan($ean): self
    {
        $this->ean = $ean;

        return $this;
    }

    public function getBrandname(): ?string
    {
        return $this->brandname;
    }

    public function setBrandname($brandname): self
    {
        $this->brandname = $brandname;

        return $this;
    }

    public function getProductname(): ?string
    {
        return $this->productname;
    }

    public function setProductname($productname): self
    {
        $this->productname = $productname;

        return $this;
    }

    public function __toString(): string 
    {
        return $this->file;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }


    public function getUpdater(): ?User
    {
        return $this->updater;
    }

    public function setUpdater(?User $updater): self
    {
        $this->updater = $updater;

        return $this;
    }


    public function getManifest()
    {
        return $this->manifest;
    }


    public function setManifest($manifest)
    {
        $this->manifest = $manifest;
        return $this;
    }


    public function getStructure()
    {
        return $this->structure;
    }


    public function setStructure($structure)
    {
        $this->structure = $structure;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIsUpdated(): bool
    {
        return $this->isUpdated;
    }

    public function setIsUpdated(bool $isUpdated): self
    {
        $this->isUpdated = $isUpdated;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}