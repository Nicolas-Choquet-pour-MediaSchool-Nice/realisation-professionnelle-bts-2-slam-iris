<?php

namespace App\Form;

use App\Entity\Coordinator;
use App\Entity\SchoolClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoordinatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'mapped' => false,
                'label' => 'Prénom',
                'data' => $options['data']->getUser() ? $options['data']->getUser()->getFirstname() : '',
            ])
            ->add('lastname', TextType::class, [
                'mapped' => false,
                'label' => 'Nom',
                'data' => $options['data']->getUser() ? $options['data']->getUser()->getLastname() : '',
            ])
            ->add('email', EmailType::class, [
                'mapped' => false,
                'label' => 'Email',
                'data' => $options['data']->getUser() ? $options['data']->getUser()->getEmail() : '',
            ])
            ->add('managedClasses', EntityType::class, [
                'class' => SchoolClass::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Classes gérées',
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coordinator::class,
        ]);
    }
}
