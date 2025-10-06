<?php

namespace App\Form;

use App\Entity\Quote;
use App\Entity\Customer;
use App\Entity\Owner;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Quote number',
            ])
            ->add('title', TextType::class, [
                'label' => 'Title',
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'companyName',
                'placeholder' => 'Select a customer',
            ])
            ->add('owner', EntityType::class, [
                'class' => Owner::class,
                'choice_label' => function (Owner $o) {
                    return trim($o->getFirstname() . ' ' . $o->getLastname()) . ' — ' . $o->getCompanyName();
                },
                'placeholder' => 'Select an owner',
                'label' => 'Owner (issuer)',
            ])
            ->add('issueDate', DateType::class, [
                'widget' => 'single_text',
                'label'  => 'Issue date',
            ])
            ->add('validUntil', DateType::class, [
                'widget' => 'single_text',
                'label'  => 'Valid until',
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft'    => 'Draft',
                    'Sent'     => 'Sent',
                    'Accepted' => 'Accepted',
                    'Declined' => 'Declined',
                    'Expired'  => 'Expired',
                ],
            ])
            // Totaux en lecture/écriture simple pour l’instant (on branchera le calcul auto avec QuoteItem ensuite)
            ->add('subTotal', MoneyType::class, [
                'required' => false,
                'label' => 'Subtotal (excl. tax)',
                'currency' => 'EUR',
                'scale' => 2,
            ])
            ->add('taxTotal', MoneyType::class, [
                'required' => false,
                'label' => 'Tax',
                'currency' => 'EUR',
                'scale' => 2,
            ])
            ->add('total', MoneyType::class, [
                'required' => false,
                'label' => 'Total (incl. tax)',
                'currency' => 'EUR',
                'scale' => 2,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}
