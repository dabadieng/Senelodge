<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
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
        $users = [];
        $genres = ['male', 'female'];
        //Gestion des utilisateurs
        for ($i = 1; $i <= 10; $i++) {

            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.png';

            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $user = new User();
            $hash = $this->encoder->encodePassword($user, 'password');

            $user
                ->setLastName($faker->lastName)
                ->setFirstName($faker->firstName($genre))
                ->setIntroduction($faker->sentence())
                ->setEmail($faker->email)
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
        }

        //Gestion des annonces
        for ($i = 0; $i <= 30; $i++) {
            $ad = new Ad();
            $user = $users[mt_rand(0, count($users) - 1)];

            $title = $faker->sentence();
            //$coverImage = $faker->imageURL();
            $coverImage = 'http://placehold.it/1000x300';
            $introduction = $faker->paragraph(2);
            $content = $faker->paragraph(5);

            $ad
                ->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(80, 200))
                ->setRooms(mt_rand(1, 6))
                ->setAuthor($user);

            for ($j = 1; $j <= mt_rand(2, 5); $j++) {
                $image = new Image();
                $image
                    ->setUrl('http://placehold.it/1000x300')
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
