<?php

namespace App\Forms;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerTypeAdress extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, [
                'label_format' => 'customer.adress.edit.%name%',
            ])
            ->add('housenumber', TextType::class, [
                'label_format' => 'customer.adress.edit.%name%',
            ])
            ->add('zipcode', TextType::class, [
                'label_format' => 'customer.adress.edit.%name%',
            ])
            ->add('city', TextType::class, [
                'label_format' => 'customer.adress.edit.%name%',
            ]);
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
