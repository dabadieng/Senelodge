<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Image;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        for ($i = 0; $i <= 30; $i++) {

            $ad = new Ad();

            $title = $faker->sentence();
            $coverImage = $faker->imageURL();
            $introduction = $faker->paragraph(2);
            $content = $faker->paragraph(5);


            $ad->setTitle($title)
                ->setCoverImage($coverImage)
                ->setIntroduction($introduction)
                ->setContent($content)
                ->setPrice(mt_rand(80, 200))
                ->setRooms(mt_rand(1, 6));

            for ($j = 1; $j <= mt_rand(2, 5); $j++) {
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                    ->setCaption($faker->sentence())
                    ->setAd($ad);
                    $manager->persist($image); 
            }

            $manager->persist($ad);
        }

        $manager->flush();
    }
}
