<?php

namespace App\Forms;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerTypeAdress extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street')
            ->add('housenumber')
            ->add('zipcode')
            ->add('city')
            ->add('telephonenumber');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
