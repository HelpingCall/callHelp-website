<?php

namespace App\Forms;

use App\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street')
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('housenumber')
            ->add('zipcode')
            ->add('city')
            ->add('telephonenumber')
            ->add('title', ChoiceType::class, [
                'choices' => [
                    'Herr' => 'Herr',
                    'Frau' => 'Frau',
                    'Divers' => 'Divers',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
