<?php

namespace AppBundle\Controller;

use AppBundle\Service\StripeManager;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\Hashids;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Stripe;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function __construct(StripeManager $stripeManager, string $secret)
    {
        $this->stripeManager = $stripeManager;
        $this->secret = $secret;
    }

    /**
     * @Route("/payment/{hashId}/confirm", name="payment_confirm")
     */
    public function confirmAction($hashId, Request $request)
    {
        // https://stripe.com/docs/sources/giropay#customer-action
        // Stripe populates the redirect[return_url] with the following GET parameters when returning your customer to your website:
        // source: a string representing the original ID of the Source object
        // livemode: indicates if this is a live payment, either true or false
        // client_secret: used to confirm that the returning customer is the same one
        //                who triggered the creation of the source (source IDs are not considered secret)

        $hashids = new Hashids($this->secret, 8);

        $decoded = $hashids->decode($hashId);
        if (count($decoded) !== 1) {
            throw new BadRequestHttpException(sprintf('Payment with hash "%s" does not exist', $hashId));
        }

        $paymentId = current($decoded);

        $payment = $this->getDoctrine()
            ->getRepository(PaymentInterface::class)
            ->find($paymentId);

        if (null === $payment) {
            throw new BadRequestHttpException(sprintf('Payment with id "%d" does not exist', $paymentId));
        }

        $clientSecret = $request->query->get('client_secret');
        $sourceId = $request->query->get('source');

        // TODO Compare sources

        if ($payment->getSourceClientSecret() !== $clientSecret) {
            throw new BadRequestHttpException(sprintf('Client secret for payment with id "%d" does not match', $paymentId));
        }

        $this->stripeManager->configure();

        $stripeAccount = $payment->getStripeUserId();
        $stripeOptions = [];

        if (null !== $stripeAccount) {
            $stripeOptions['stripe_account'] = $stripeAccount;
        }

        $source = Stripe\Source::retrieve($sourceId, $stripeOptions);

        // The source is already chargeable
        // Redirect to order confirmation page
        if ('chargeable' === $source->status) {
            // var_dump('OKKKK');
            // TODO Send redirect
        }

        return $this->render('@App/order/wait_for_payment.html.twig', [
            'order' => $payment->getOrder(),
            'shipping_time' => $payment->getOrder()->getShippedAt(),
            'source_id' => $sourceId,
            'client_secret' => $clientSecret,
            'stripe_options' => $stripeOptions,
        ]);
    }
}
