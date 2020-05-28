<?php

namespace App\Forms;

use App\Entity\Medicals;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,['label_format' => '%name%',])
            ->add('dosis', NumberType::class, [
                'html5' => true,
                'row_attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'hidden'],
                'label_format' => '%name%',
            ])
            ->add('link', UrlType::class, [
                'required' => false,
                'label_format' => '%name%',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Medicals::class);
    }
}
