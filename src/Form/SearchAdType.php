<?php

namespace App\Form;

use App\Entity\Localisation;
use App\Entity\SearchAd;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class SearchAdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('localisation',  EntityType::class, array(
                'required' => false,
                'label' => "Localisation : ",
                'class'    => Localisation::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true
            ))
            ->add('rooms', IntegerType::class, [
                'required' => false,
                'label' => "Nombre de chambres ",
                'attr' => [
                    'placeholder' => 'Entrer le nombre de chambres souhaités '
                ]
            ])
            ->add('maxPrice', IntegerType::class, [
                'required' => false,
                'label' => "Prix maximal",
                'attr' => [
                    'placeholder' => 'Budget max'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchAd::class,
            'method' => 'get',
            'csrf_protection' => false
        ]);
    } 
}
