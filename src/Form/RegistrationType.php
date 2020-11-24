<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration('Prénom', 'Votre Prénom')
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration('Nom', 'Votre Nom')
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration('Email', 'Votre Email')
            )
            ->add(
                'picture',
                UrlType::class,
                $this->getConfiguration(
                    'Photo profil',
                    "Entrer l'URL de votre photo profil"
                )
            )
            ->add(
                'hash',
                PasswordType::class,
                $this->getConfiguration(
                    'Mot de Passe',
                    'Entrer votre mot de passe'
                )
            )
            ->add(
                'passwordConfirm',
                PasswordType::class,
                $this->getConfiguration(
                    'Confirmer votre mot de passe',
                    'Veuillez confirmer votre mot de passe'
                )
            )
            ->add(
                'introduction',
                TextType::class,
                $this->getConfiguration(
                    'Introduction',
                    'Présentez-vous en quelques mots'
                )
            )
            ->add(
                'description',
                TextareaType::class,
                $this->getConfiguration(
                    'Description',
                    "C'est le moment de vous présenter en détail"
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
