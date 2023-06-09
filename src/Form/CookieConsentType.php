<?php

namespace App\Form;

use App\Entity\CookieConsent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CookieConsentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('analyticsConsent', CheckboxType::class, [
                'required' => false,
            ])
            ->add('marketingConsent', CheckboxType::class, [
                'required' => false,
            ])
            ->add('ipAddress', HiddenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CookieConsent::class,
        ]);
    }
}
