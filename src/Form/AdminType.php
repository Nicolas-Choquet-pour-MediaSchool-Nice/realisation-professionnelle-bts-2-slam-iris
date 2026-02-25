<?php

namespace App\Form;

use App\Entity\Admin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminType extends AbstractType
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Admin::class,
        ]);
    }
}
