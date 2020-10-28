<?php

namespace App\Form;

use App\Entity\Ad;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdType extends AbstractType
{
    /**
     * Fonction qui permet de mettre en place les labels et les placeholder
     *
     * @param [string] $label
     * @param [string] $placeholder
     * @param array $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = [])
    {
        return array_merge(
            [
                'label' => $label,
                'attr' => [
                    'placeholder' => $placeholder,
                ],
            ],
            $options
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                $this->getConfiguration('Titre', 'Entrer un super titre')
            )
            ->add(
                'slug',
                TextType::class,
                $this->getConfiguration(
                    'Adresse web',
                    'Entrer votre adresse web (automatique)',
                    [
                        'required' => false,
                    ]
                )
            )
            ->add(
                'coverImage',
                UrlType::class,
                $this->getConfiguration(
                    'Entrer une URL',
                    "Adresse URL de l'image "
                )
            )
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
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
