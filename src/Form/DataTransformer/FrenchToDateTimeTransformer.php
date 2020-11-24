<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{

    /**
     * Transforme une DateTime en FrenchDate
     * La donnée part du form
     * $form = $this->createForm(BookingType::class, $booking);
     *
     */
    public function transform($date)
    {
        if ($date === null) {
            return "";
        }
        return $date->format("d/m/Y");
    }

    /**
     * Transforme une FrenchDate en DateTime 
     * La donnée vient vers le formulaire 
     * $form->handleRequest($request); 
     *
     */
    public function reverseTransform($frenchDate)
    {
        if ($frenchDate === null) {
            //Exception fournie pas symfony 
            throw new TransformationFailedException("Vous devez fournir une date");
        }

        $date = \DateTime::createFromFormat("d/m/Y", $frenchDate);

        /**
         * Si la date ne correspond pas au format attendue la méthode retourne false ex 21/10/2020 en 21-10-2020
         */
        if ($frenchDate === false) {
            //Exception
            throw new TransformationFailedException("Le format est incorrect ");
        }

        return $date;
    }
}
