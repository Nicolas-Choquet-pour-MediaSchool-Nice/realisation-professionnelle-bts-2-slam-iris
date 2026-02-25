<?php

namespace App\Form;

use App\Entity\Coordinator;
use App\Entity\SchoolClass;
use App\Entity\Student;
use App\Repository\StudentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddStudentToSchoolClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('students', EntityType::class, [
            'class' => Student::class,
            'query_builder' => function (StudentRepository $sr) {
                return $sr->createQueryBuilder('s')
                    ->where('s.class IS NULL');
            },
            'multiple' => true,
            'expanded' => false,
            'choice_label' => function (Student $student) {
                return $student->getUser()->getFirstname() . ' ' . $student->getUser()->getLastname();
            },
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SchoolClass::class,
        ]);
    }
}
