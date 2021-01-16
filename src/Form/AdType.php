<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                $this->getConfiguration('Titre', 'Entrer un super titre')
            )
            /*->add(
                'slug',
                TextType::class,
                $this->getConfiguration(
                    'Adresse web',
                    'Entrer votre adresse web (automatique)',
                    [
                        'required' => false,
                    ]
                )
            )*/
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
                'content',
                TextareaType::class,
                $this->getConfiguration(
                    'Description détaillée',
                    'Entrer une description detaillée'
                )
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
