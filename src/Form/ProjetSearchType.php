<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Etiquette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required'   => false,
                'label'      => 'Nom',
                'attr'       => ['placeholder' => 'Rechercher par nom...'],
            ])

            ->add('statut', ChoiceType::class, [
                'required'    => false,
                'placeholder' => '-- Tous les statuts --',
                'choices'     => [
                    'Planifié' => 'planifie',
                    'En cours' => 'en_cours',
                    'Terminé'  => 'termine',
                    'Annulé'   => 'annule',
                ],
            ])

            ->add('createur', EntityType::class, [
                'class'        => User::class,
                'choice_label' => 'pseudo',
                'required'     => false,
                'placeholder'  => '-- Tous les créateurs --',
            ])

            ->add('etiquette', EntityType::class, [
                'class'        => Etiquette::class,
                'choice_label' => 'nom',
                'required'     => false,
                'placeholder'  => '-- Toutes les étiquettes --',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => null,
            'csrf_protection' => false,
            'method'          => 'GET',
        ]);
    }

    public function getBlockPrefix(): string
    {
        // Préfixe vide = paramètres GET plats : ?nom=...&statut=...
        return '';
    }
}
