<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Etiquette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProjetSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom', TextType::class, [
                'required' => false
            ])

            ->add('statut', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Planifié' => 'planifie',
                    'En cours' => 'en_cours',
                    'Terminé' => 'termine',
                    'Annulé' => 'annule',
                ]
            ])
            ->add('createur', EntityType::class, [
    'class' => User::class,
    'choice_label' => 'pseudo',
    'required' => false
            ])
            ->add('etiquette', EntityType::class, [
    'class' => Etiquette::class,
    'choice_label' => 'nom',
    'required' => false
            ]);
    }
}