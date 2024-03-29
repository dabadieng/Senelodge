<?php

namespace App\Entity;

use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Repository\AdRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass=AdRepository::class)
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(
 *      fields={"title"},
 *      message="Une autre annonce posséde déjà ce titre, merci de le modifier SVP "
 * )
 */
class Ad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Assert\Length(
     *      min = 10,
     *      max = 255,
     *      minMessage = "Le titre doit faire plus de 10 caractères",
     *      maxMessage = "Le titre doit faire moins de 255 caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *      min = 10,
     *      minMessage = "Votre introduction doit contenir au minimun 10 caractères"
     * )
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(
     *      min = 20,
     *      minMessage = "Votre description doit contenir au minimun 20 caractères"
     * )
     */
    private $description;


    /**
     * @ORM\Column(type="integer")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="ad", orphanRemoval=true, cascade={"persist"})
     * @Assert\All({
     *      @Assert\Image(
     *              mimeTypes = {"image/jpeg", "image/gif", "image/png"},
     *              mimeTypesMessage = "Le type d'extension de photo doit être JPEG/GIF/PNG     veuillez retirer celles qui ne respectent pas ce format"
     *      )
     * })
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="ad")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="ad", orphanRemoval=true)
     */
    private $descriptions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $coverImage;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="ad", fileNameProperty="coverImage", size="documentSize", mimeType="extension", originalName="path")
     * @var File|null
     * @Assert\File(
     *     maxSize = "4096k",
     *     mimeTypes = {"application/pdf", 
     *                  "image/jpeg", "image/png",
     *                   },
     *     mimeTypesMessage = "Please upload a valid document file(pdf) ou image file(img/jpg/bmp)")
     */
    private $documentFile;

    /** 
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * NOTE:mimetype
     * @ORM\Column(type="string", length=255)
     */
    private $extension;

    /**
     * @ORM\Column(type="integer")
     *
     * //@var int|null
     */
    private $documentSize;


    /**
     * @ORM\ManyToOne(targetEntity=Localisation::class, inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $localisation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
    }

    /**
     * perrmet d'initialiser un slug
     * @ORM\PrePersist
     * ORM\PreUpdate
     *
     * @return void
     */
    public function initializeSlug()
    {
        if (empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->Slugify($this->title);
        }

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvgRatings()
    {
        /*Calculer la somme des notations
        *$this->descriptions est un type collection en utilisant la function toArray() permet
        *de le transformer en un Array 
        */
        $sum = array_reduce($this->descriptions->toArray(), function ($total, $comment) {
            return $total + $comment->getRating();
        }, 0); //en mettant 0 cela initialise le total à 0

        //Faire la division pour avoir la moyenne 
        if (count($this->descriptions) > 0) return $sum / count($this->descriptions);

        return 0;
    }

    /**
     * Permet d'obtenir un tableau des jours qui ne sont pas disponible pour ce annonce
     *
     * @return array un tableau d'objet dateTime représentant les jours d'occupations
     */
    public function getNotAvailableDays()
    {
        /**
         * fonction range()
         * $resultat = range(10, 20, 5);
         * $resultat = [10, 15, 20]; 
         */
        $notAvailableDays = [];

        foreach ($this->bookings as $booking) {
            // Calculer les jours qui se trouvent entre la date d'arrivée et de départ
            $resultat = range(
                $booking->getStartDate()->getTimestamp(),
                $booking->getEndDate()->getTimestamp(),
                24 * 60 * 60
            );

            //array_pap transforme un tab en un autre tab grace à une function
            $days = array_map(function ($dayTimestamp) {
                return new \DateTime(date('Y-m-d', $dayTimestamp));
            }, $resultat);

            //ajoute le tab days au tab notAvailableDays
            $notAvailableDays = array_merge($notAvailableDays, $days);
        }

        return $notAvailableDays;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

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


    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getdescriptions(): Collection
    {
        return $this->descriptions;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->descriptions->contains($comment)) {
            $this->descriptions[] = $comment;
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->descriptions->contains($comment)) {
            $this->descriptions->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }

    /**
     * Permet de récupérer le commentaire d'un auteur par rapport à une annonce 
     *
     * @param User $author
     * @return Comment|null
     */
    public function getCommentFromAuthor(User $author)
    {
        foreach ($this->descriptions as $comment) {
            if ($comment->getAuthor() == $author) return $comment;
        }
        return null;
    }

    public function getCoverImage(): ?string
    {
/*
        if (count($this->images) > 0) {
            $cov = array_reduce($this->images->toArray(), function ($c, $image) {
                if ($c == 0) {
                    $this->coverImage = $image->getUrl();
                    return $this->coverImage;
                }
            }, 0); //en mettant 0 cela initialise par défaut le total à 0

        }
*/
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getLocalisation(): ?Localisation
    {
        return $this->localisation;
    }

    public function setLocalisation(?Localisation $localisation): self
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;

        if (null !== $documentFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    public function setDocumentSize(?int $documentSize): void
    {
        $this->documentSize = $documentSize;
    }

    public function getDocumentSize(): ?int
    {
        return $this->documentSize;
    }

    /**
     * Get nOTE:mimetype
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set nOTE:mimetype
     *
     * returnself
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * returnself
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
