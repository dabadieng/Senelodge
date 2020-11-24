<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;

class ApplicationType extends AbstractType
{
    /**
     * Fonction qui permet de mettre en place les labels et les placeholder
     *
     * @param [string] $label
     * @param [string] $placeholder
     * @param array $options
     * @return array
     */
    protected function getConfiguration($label, $placeholder, $options = [])
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
}
