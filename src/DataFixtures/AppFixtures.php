<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\Localisation;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');
        $adminRole = new Role();
        $adminRole->setTitle("ROLE_ADMIN");
        $manager->persist($adminRole);

        $localisations = ["Dakar", "Diourbel", "Fatick", "Kaolack", "Kolda", "Louga", "Matam", "Saint-Louis", "Tambacounda", "Thiès", "Ziguinchor", "Kaffrine", "Kédougou", "Sédhiou"];

        $adminUser = new User();
        $adminUser
            ->setLastName("DIENG")
            ->setFirstName("Daba")
            ->setIntroduction($faker->sentence())
            ->setEmail("dieng.daba@outlook.com")
            ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
            ->setHash($this->encoder->encodePassword($adminUser, "123456"))
            ->setPicture("dieng.jpg")
            ->addUserRole($adminRole);
        $manager->persist($adminUser);

        $users = [];
        $genres = ['male', 'female'];

        //Gestion des utilisateurs
        for ($i = 1; $i <= 40; $i++) {
            /**
             * use API randomuser
             * $genre = $faker->randomElement($genres);
             *$picture = 'https://randomuser.me/api/portraits/';
             *$pictureId = $faker->numberBetween(1, 99) . '.jpg';
             *$picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

             */

            $user = new User();
            //le 1er paramètre correspond à l'entité déclarée dans le security.yml
            $hash = $this->encoder->encodePassword($user, 'password');
            if ($i <= 20) {
                $user
                    ->setLastName($faker->lastName)
                    ->setFirstName($faker->firstName("male"))
                    ->setIntroduction($faker->sentence())
                    ->setEmail($faker->email)
                    ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                    ->setHash($hash)
                    ->setPicture($i . ".jpg");

                $manager->persist($user);
                $users[] = $user;
            } else {
                $user
                    ->setLastName($faker->lastName)
                    ->setFirstName($faker->firstName("female"))
                    ->setIntroduction($faker->sentence())
                    ->setEmail($faker->email)
                    ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                    ->setHash($hash)
                    ->setPicture($i . ".jpg");

                $manager->persist($user);
                $users[] = $user;
            }
        }
        for ($a = 0; $a < count($localisations); $a++) {
            $localisation = new Localisation();
            $localisation->setName($localisations[$a]);


            //Gestion des annonces
            for ($i = 0; $i <= 30; $i++) {
                $ad = new Ad();
                $user = $users[mt_rand(0, count($users) - 1)];

                $title = $faker->sentence();
                $coverImage = $faker->imageURL();
                $coverImage = mt_rand(1, 25) . ".png";
                $introduction = $faker->paragraph(2);
                $content = $faker->paragraph(5);

                $ad
                    ->setTitle($title)
                    ->setCoverImage($coverImage)
                    ->setIntroduction($introduction)
                    ->setDescription($content)
                    ->setPrice(mt_rand(80, 200))
                    ->setRooms(mt_rand(1, 6))
                    ->setAuthor($user)
                    ->setLocalisation($localisation);

                //Gestion des images attachées aux annonces
                for ($j = 1; $j <= mt_rand(2, 5); $j++) {
                    $image = new Image();
                    $image
                        ->setUrl(mt_rand(1, 50) . ".png")
                        ->setCaption($faker->sentence())
                        ->setAd($ad);
                    $manager->persist($image);
                }

                //Gestion des réservations attachées auw annonces 
                for ($j = 1; $j <= mt_rand(0, 10); $j++) {
                    $booking = new Booking();

                    //Gestion des dates
                    $createdAt = $faker->dateTimeBetween("-6 months");
                    $startDate = $faker->dateTimeBetween("-3 months");

                    //création d'une durée au hasard
                    $duration = mt_rand(3, 10);

                    //il faut cloner la startDate sinon cà vas la modifier 
                    $endDate = (clone $startDate)->modify("+$duration days");

                    //calcul du montant à payer 
                    $amount = $ad->getPrice() * $duration;

                    //choisir un utilisateur au hasard 
                    $booker = $users[mt_rand(0, count($users) - 1)];

                    $booking->setBooker($booker)
                        ->setAd($ad)
                        ->setStartDate($startDate)
                        ->setEndDate($endDate)
                        ->setAmount($amount)
                        ->setCreatedAt($createdAt)
                        ->setComment($faker->paragraph(2));
                    $manager->persist($booking);

                    //Gestion des commentaires 
                    if (mt_rand(0, 1)) {
                        $comment = new Comment();
                        $comment->setAd($ad)
                            ->setAuthor($booker)
                            ->setContent($faker->paragraph())
                            ->setRating(mt_rand(1, 5));

                        $manager->persist($comment);
                    }
                }

                $manager->persist($ad);
            }
            $manager->persist($localisation);
            $manager->flush();
        }

        $manager->flush();
    }
}
