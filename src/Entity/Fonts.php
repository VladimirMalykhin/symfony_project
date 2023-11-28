<?php

namespace App\Entity;

use App\Repository\FontsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FontsRepository::class)
 */
class Fonts
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
    private $font_family;

   /**
     * @ORM\Column(type="string", length=255)
     */
    private $folder;

    /**
     * @ORM\Column(type="integer", precision=3, scale=2)
     */
    private $font_weight;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $font_style;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFontFamily(): ?string
    {
        return $this->font_family;
    }

    public function setFontFamily(string $font_family): self
    {
        $this->font_family = $font_family;

        return $this;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFontWeight(): ?string
    {
        return $this->font_weight;
    }

    public function setFontWeight(string $font_weight): self
    {
        $this->font_weight = $font_weight;

        return $this;
    }

    public function getFontStyle(): ?string
    {
        return $this->font_style;
    }

    public function setFontStyle(?string $font_style): self
    {
        $this->font_style = $font_style;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function __toString(): string 
    {
        return $this->file;
    }
}
