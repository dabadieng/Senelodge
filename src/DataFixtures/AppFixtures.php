<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
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
        $adminRole = new Role();
        $adminRole->setTitle("ROLE_ADMIN");
        $manager->persist($adminRole);

        $adminUser = new User();
        $adminUser
            ->setLastName("DIENG")
            ->setFirstName("Daba")
            ->setIntroduction($faker->sentence())
            ->setEmail("daba@symfony.com")
            ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
            ->setHash($this->encoder->encodePassword($adminUser, "123456"))
            ->setPicture("https://randomuser.me/api/portraits/women/85.jpg")
            ->addUserRole($adminRole);
        $manager->persist($adminUser);

        $users = [];
        $genres = ['male', 'female'];

        //Gestion des utilisateurs
        for ($i = 1; $i <= 10; $i++) {

            $genre = $faker->randomElement($genres);

            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture .= ($genre == 'male' ? 'men/' : 'women/') . $pictureId;

            $user = new User();
            //le 1er paramètre correspond à l'entité déclarée dans le security.yml
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
            $coverImage = $faker->imageUrl();
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
                    ->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
