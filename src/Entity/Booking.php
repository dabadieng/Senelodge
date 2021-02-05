<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookingRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity=Ad::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("\DateTimeInterface")
     * @Assert\GreaterThan("today", message="La date d'arrivée doit être ultérieure à la date d'aujourd'hui !", groups={"front"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("\DateTimeInterface")
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ doit être plus *éloignée que la date d'arrivée !")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Callback appelé à chaque fois que l'on créer une reservation
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @return void
     */
    public function prePersist()
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }

        if (empty($this->amount)) {
            //le montant à payer durée * prix 
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    public function getDuration()
    {

        //diff est un objet de type date intervale
        $diff = $this->endDate->diff($this->startDate);

        return $diff->days;
    }


    public function isBookableDates()
    {
        /*1) il faut connaitre les dates qui sont impossible pour l'annonce 
            créer une fonctionnalité dans l'entité annonce (ad)
        */
        $notAvailableDays = $this->ad->getNotAvailableDays();

        //2) comparer les dates choisies avec les dates impossibles 
        $bookingDays = $this->getDays();

        /**
         * pour comparer plus simplement transformer les 2 tabs
         * en des chaines de caractères 
         **/

        /**
         * factorisation de la fonction 
         *   $days = array_map(function ($day) {
         *    return $day->format('Y-m-d');
         *}, $bookingDays);
         */
        $formatDays = function ($day) {
            return $day->format('Y-m-d');
        };

        //tab qui contient des chaines de caractères de mes journées 
        $days = array_map($formatDays, $bookingDays);

        //tab qui contient des chaines de caractères 
        $notAvailable = array_map($formatDays, $notAvailableDays);

        //3) retourner true ou false en fonction des disponibilités
        foreach ($days as $day) {
            //si la recherche est strictement égale à faux donc retourne faux 
            if (array_search($day, $notAvailable) !== false) return false;
        }
        return true;
    }

    /**
     * allows you to retrieve all the days corresponding to my reservation 
     *
     * @return array un tableau d'objet DateTime 
     */
    public function getDays()
    {
        //récupère un tab des dates sous forme de Timestamp
        $resultat = range(
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60
        );

        //retourne un tab sous forme de DateTime 
        $days = array_map(function ($dayTimestamp) {
            return new \DateTime(date('Y-m-d', $dayTimestamp));
        }, $resultat);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
