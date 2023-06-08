<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DeliveryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delivery_date', DateType::class, [
                'required'   => true,
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => false,
                'property_path' => "delivery_date_notimmutable",
            ])
            ->add('delivery_address', null, [
                'required'   => true,
            ])
            ->add('latitude_address', HiddenType::class, [
                'mapped' => false,
                'property_path' => "latitude_address",
            ])
            ->add('longitude_address', HiddenType::class, [
                'mapped' => false,
                'property_path' => "longitude_address",
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
