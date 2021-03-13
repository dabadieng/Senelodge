<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\All({
     *      @Assert\Image(
     *              mimeTypes = {"image/jpeg", "image/gif", "image/png"},
     *              mimeTypesMessage = "Le type d'extension de photo doit être JPEG/GIF/PNG     veuillez retirer celles qui ne respectent pas ce format"
     *      )
     * })
     *  
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Votre titre doit contenir au minimun 10 caractères"
     * )
     */
    private $caption;

    /**
     * @ORM\ManyToOne(targetEntity=ad::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getAd(): ?ad
    {
        return $this->ad;
    }

    public function setAd(?ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }
}
