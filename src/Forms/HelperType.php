<?php

namespace App\Forms;

use App\Entity\Helper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HelperType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label_format' => 'helper.edit.%name%',
            ])
            ->add('lastname', TextType::class, [
                'label_format' => 'helper.edit.%name%',
            ])
            ->add('email', EmailType::class, [
                'label_format' => 'helper.edit.%name%',
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
        $resolver->setDefault('data_class', Helper::class);
    }
}
