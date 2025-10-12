<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Product / service',
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Qty',
                'scale' => 2,
                'html5' => true,
                'attr' => ['step' => '0.01', 'min' => '0'],
                'empty_data' => '0',        // ← évite null
            ])
            ->add('unitPrice', MoneyType::class, [
                'label' => 'Unit price (excl. tax)',
                'currency' => 'EUR',
                'scale' => 2,
                'attr' => ['min' => '0'],
                'empty_data' => '0',        // ← évite null
            ])
            ->add('taxRate', ChoiceType::class, [
                'label' => 'VAT',
                'choices' => [
                    '0%'   => 0.00,
                    '5.5%' => 0.055,
                    '10%'  => 0.10,
                    '20%'  => 0.20,
                ],
                // si tu veux une valeur par défaut :
                // 'data' => 0.20,
            ])
        ;
        // NB: on ne met pas 'total' ici, il sera calculé à l’enregistrement
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }
}
