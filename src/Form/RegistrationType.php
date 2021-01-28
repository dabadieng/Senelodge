<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            /*
            ->add(
                'picture',
                UrlType::class,
                $this->getConfiguration(
                    'Photo profil',
                    "Entrer l'URL de votre photo profil"
                )
            )
            */
            ->add('picture', FileType::class, [
                'label' => "Photo profil",

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // Add multiple
                'multiple' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                /*
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            "image/png",
                            "image/jpeg",
                            "image/jpg",
                            "image/gif",
                            "image/x-citrix-jpeg",
                            "image/x-citrix-png",
                            "image/x-png",
                        ],
                        'mimeTypesMessage' => "Ce type de fichier n'est pas autorisé",
                    ])
                    
                ],*/
            ])
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
