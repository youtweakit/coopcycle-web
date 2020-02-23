<?php

namespace AppBundle\Form;

use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationType extends AbstractType
{
    private $isDemo;

    public function __construct(bool $isDemo = false)
    {
        $this->isDemo = $isDemo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('givenName', TextType::class, array('label' => 'profile.givenName'))
            ->add('familyName', TextType::class, array('label' => 'profile.familyName'))
            // Phone number will be asked during checkout
            // @see AppBundle\Form\Checkout\CheckoutAddressType
            ;

        if ($this->isDemo) {
            $builder->add('accountType', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'choices'  => [
                    'roles.ROLE_USER' => 'CUSTOMER',
                    'roles.ROLE_COURIER' => 'COURIER',
                    'roles.ROLE_RESTAURANT' => 'RESTAURANT',
                    'roles.ROLE_STORE' => 'STORE',
                ],
                'label' => 'profile.accountType'
            ]);
        }

        // @see https://fr.sendinblue.com/blog/guide-opt-in/
        // @see https://mailchimp.com/fr/help/collect-consent-with-gdpr-forms/
        // @see https://www.mailerlite.com/blog/how-to-create-opt-in-forms-that-still-work-under-gdpr
        $builder->add('newsletterOptin', CheckboxType::class, [
            'label'    => 'J\'accepte de recevoir la newsletter de XXX',
            'required' => false,
            'mapped'   => false,
        ]);
        $builder->add('promotionsOptin', CheckboxType::class, [
            'label'    => 'J\'accepte de recevoir des promotions ponctuelles (livraison offerte…)',
            'required' => false,
            'mapped'   => false,
        ]);
        // $builder->add('marketingOptin', CheckboxType::class, [
        //     'label'    => 'J\'accepte de recevoir des emails ciblés en fonction de mes données de navigation',
        //     'required' => false,
        //     'mapped'   => false,
        // ]);

        // Add help to "username" field
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $child = $form->get('username');
            $config = $child->getConfig();
            $options = $config->getOptions();
            $options['help'] = 'form.registration.username.help';
            $form->add('username', get_class($config->getType()->getInnerType()), $options);
        });
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }
}
