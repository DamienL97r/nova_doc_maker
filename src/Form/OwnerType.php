<?php

namespace App\Form;

use App\Entity\Owner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as T;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OwnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', T\TextType::class, ['label' => 'First name'])
            ->add('lastname',  T\TextType::class, ['label' => 'Last name'])
            ->add('sirene',    T\TextType::class, ['label' => 'SIRENE'])
            ->add('ape',       T\TextType::class, ['label' => 'APE', 'required' => false])
            ->add('email',     T\EmailType::class, ['required' => false])
            ->add('phone',     T\TextType::class, ['required' => false, 'label' => 'Phone'])
            ->add('companyName',  T\TextType::class, ['label' => 'Company name'])
            ->add('logo',  T\TextType::class, ['label' => 'logo']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Owner::class,
        ]);
    }
}
