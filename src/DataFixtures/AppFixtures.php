<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Fonts;
use App\Entity\FormatFonts;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
    	$user = new User();
    	$user->setUsername('admin');
        $user->setRoles(['admin']);
    	$user->setPassword('admin4231');
    	$manager->persist($user);
        $user2 = new User();
        $user2->setUsername('test_user');
        $user2->setRoles([]);
        $user2->setPassword('test_user');
        $manager->persist($user2);
        $date =  new \DateTime();
        $font = new Fonts();
        $font->setFontFamily('Helvetica');
        $font->setFontWeight(400);
        $font->setFontStyle('normal');
        $font->setCreatedAt($date);
        $font->setUpdatedAt($date);
        $format1 = new FormatFonts();
        $format1->setTitle('woff');
        $format1->setUrl("https://24dvlp.com/24stream_constructor_api/fonts/helvetica_neue-regular.woff");
        $format1->setFont($font);
        $font2 = new Fonts();
        $font2->setFontFamily('Helvetica');
        $font2->setFontWeight(700);
        $font2->setFontStyle('normal');
        $font2->setCreatedAt($date);
        $font2->setUpdatedAt($date);
        $format2 = new FormatFonts();
        $format2->setTitle('woff');
        $format2->setUrl("https://24dvlp.com/24stream_constructor_api/fonts/helvetica_neue-bold.woff");
        $format2->setFont($font2);
        $font3 = new Fonts();
        $font3->setFontFamily('Montserrat');
        $font3->setFontWeight(400);
        $font3->setFontStyle('normal');
        $font3->setCreatedAt($date);
        $font3->setUpdatedAt($date);
        $format3 = new FormatFonts();
        $format3->setTitle('woff');
        $format3->setUrl("https://24dvlp.com/24stream_constructor_api/fonts/montserrat-regular.woff");
        $format3->setFont($font3);
        $manager->persist($font);
        $manager->persist($font2);
        $manager->persist($font3);
        $manager->persist($format1);
        $manager->persist($format2);
        $manager->persist($format3);
        $manager->flush();
    }
}
