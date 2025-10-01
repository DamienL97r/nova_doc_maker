<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company / Organization',
            ])
            ->add('sirene', TextType::class, [
                'label' => 'SIRENE / SIRET',
            ])
            ->add('ape', TextType::class, [
                'label' => 'APE / NAF',
                'attr'  => ['placeholder' => '6201Z'],
            ])
            ->add('vatNumber', TextType::class, [
                'label' => 'VAT number',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
