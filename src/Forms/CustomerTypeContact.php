<?php

namespace App\Forms;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerTypeContact extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label_format' => 'customer.contact.edit.%name%',
            ])
            ->add('lastname',
                TextType::class, [
                    'label_format' => 'customer.contact.edit.%name%',
                ])
            ->add('email', EmailType::class, [
                'label_format' => 'customer.contact.edit.%name%',
            ])
            ->add('telephonenumber', TelType::class, [
                'label_format' => 'customer.contact.edit.%name%',
            ])
            ->add('title', ChoiceType::class, [
                'choices' => [
                    'Herr' => 'Herr',
                    'Frau' => 'Frau',
                    'Divers' => 'Divers',
                ],
                'label' => false,
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
