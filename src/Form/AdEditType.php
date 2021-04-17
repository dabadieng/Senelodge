<?php

namespace App\Form;

use App\Entity\Ad;
use App\Entity\Localisation;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdEditType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("localisation", EntityType::class, [
                'class' => Localisation::class,
                'choice_label' => function ($localisation) {
                    return $localisation->getName();
                }
            ])
            ->add(
                'title',
                TextType::class,
                $this->getConfiguration('Titre', 'Entrer un super titre')
            )
            ->add('coverImage', FileType::class, [
                'label' => "Photo principale",

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // Add multiple
                'multiple' => true,

                'attr' => ['placeholder' => 'Sélectionner au maximum 1 photo principale '],

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes

                'constraints' => [
                    new Count([
                        'max' => 1,
                        'maxMessage' => 'Vous pouvez sélectionner maximum 1 photo'
                    ]),
                    new All([
                        new File([
                            'maxSize' => '50M',
                            'mimeTypes' => [
                                "image/png",
                                "image/jpeg",
                                "image/jpg",
                                "image/gif",
                                "image/x-citrix-jpeg",
                                "image/x-citrix-png",
                                "image/x-png",
                            ],
                            'maxSizeMessage' => 'La taille de certaines photos est trop grande',
                            'mimeTypesMessage' => 'Certains fichiers ne respectent pas les types autorisés ',
                        ])
                    ])
                ],
            ])
            /*
            ->add(
                'coverImage',
                UrlType::class,
                $this->getConfiguration(
                    'Entrer une URL',
                    "Adresse URL de l'image "
                )
            )
            */
            ->add(
                'introduction',
                TextType::class,
                $this->getConfiguration(
                    'Introduction',
                    'Entrer une description globale de votre bien'
                )
            )
            ->add(
                'description',
                TextareaType::class,
                $this->getConfiguration("Description détaillée", "Tapez une description qui donne vraiment envie de venir chez vous !")
            )
            ->add(
                'rooms',
                IntegerType::class,
                $this->getConfiguration(
                    'Nombre de chambres',
                    'Entrer le nombre de chambres'
                )
            )
            ->add(
                'price',
                MoneyType::class,
                $this->getConfiguration(
                    'Prix par Nuit',
                    'Entrer le prix que vous souhaitez par nuit'
                )
            )
            //On ajoute le champs image qui ne doit pas mapped dans la db 
            // [mapped à false ]
            ->add('images', FileType::class, [
                'label' => "Selectionner vos photos",

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // Add multiple
                'multiple' => true,

                'attr' => ['placeholder' => 'Sélectionner au maximum 5 photos '],

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes

                'constraints' => [
                    new Count([
                        'max' => 5,
                        'maxMessage' => 'Vous pouvez sélectionner maximum 5 photos'
                    ]),
                    new All([
                        new Image([
                            'maxSize' => '1028M',
                            'mimeTypes' => [
                                "image/png",
                                "image/jpeg",
                                "image/jpg",
                                "image/gif",
                                "image/x-citrix-jpeg",
                                "image/x-citrix-png",
                                "image/x-png",
                            ],
                            'maxSizeMessage' => 'La taille de certaines photos est trop grande',
                            'mimeTypesMessage' => 'Certains fichiers ne respectent pas les types autorisés ',
                        ])
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
