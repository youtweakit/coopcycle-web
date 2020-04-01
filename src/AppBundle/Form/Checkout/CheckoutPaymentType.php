<?php

namespace AppBundle\Form\Checkout;

use AppBundle\Form\StripePaymentType;
use AppBundle\Service\StripeManager;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class CheckoutPaymentType extends AbstractType
{
    private $stripeManager;
    private $country;
    private $debug;

    public function __construct(StripeManager $stripeManager, string $country, bool $debug = false)
    {
        $this->stripeManager = $stripeManager;
        $this->country = $country;
        $this->debug = $debug;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stripePayment', StripePaymentType::class, [
                'mapped' => false,
            ]);

        // TODO Enable per restaurant
        if ($this->debug || 'de' === $this->country) {

            $choices = [
                'Credit card' => 'card',
                'Giropay' => 'giropay',
            ];

            $builder
                ->add('method', ChoiceType::class, [
                    'label' => 'form.checkout_payment.method.label',
                    'choices' => $choices,
                    'mapped' => false,
                    'expanded' => true,
                    'multiple' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {

            $form = $event->getForm();
            $order = $event->getData();

            if ('giropay' === $form->get('method')->getData()) {

                $payment = $order->getLastPayment(PaymentInterface::STATE_CART);

                // TODO Catch Exception (source not enabled)
                $source = $this->stripeManager->createGiropaySource($payment);

                $payment->setSource($source);
            }
        });
    }
}
