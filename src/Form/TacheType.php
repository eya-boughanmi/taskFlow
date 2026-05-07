<?php

namespace App\Form;

use App\Entity\Etiquette;
use App\Entity\Tache;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Basse'   => 'basse',
                    'Moyenne' => 'moyenne',
                    'Haute'   => 'haute',
                    'Urgente' => 'urgente',
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'À faire'  => 'a_faire',
                    'En cours' => 'en_cours',
                    'Terminée' => 'terminee',
                ]
            ])
            ->add('dateEcheance')
            ->add('assigneA', EntityType::class, [
                'class'        => User::class,
                'choice_label' => 'email',
                'required'     => false,
                'placeholder'  => '-- Non assignée --',
            ])
            ->add('etiquettes', EntityType::class, [
                'class'        => Etiquette::class,
                'choice_label' => 'nom',
                'multiple'     => true,
                'expanded'     => true,
                'by_reference' => false,
                'required'     => false,
            ])
            ->add('pieceJointe', FileType::class, [
                'mapped'   => false,
                'required' => false,
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
