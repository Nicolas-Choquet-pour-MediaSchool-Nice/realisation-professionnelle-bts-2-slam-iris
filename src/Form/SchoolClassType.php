<?php

namespace App\Form;

use App\Entity\Coordinator;
use App\Entity\SchoolClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchoolClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom de la classe',
            ])
            ->add('coordinators', EntityType::class, [
                'class' => Coordinator::class,
                'choice_label' => function (Coordinator $coordinator) {
                    return $coordinator->getUser()->getFirstname() . ' ' . $coordinator->getUser()->getLastname();
                },
                'multiple' => true,
                'label' => 'Coordinateurs',
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SchoolClass::class,
        ]);
    }
}
