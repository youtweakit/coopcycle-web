<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\Address;
use AppBundle\Entity\Base\GeoCoordinates;
use AppBundle\Entity\Delivery;
use AppBundle\Entity\Delivery\PricingRule;
use AppBundle\Entity\Delivery\PricingRuleSet;
use AppBundle\Entity\Restaurant;
use AppBundle\Entity\Sylius\Order;
use AppBundle\Exception\ShippingAddressMissingException;
use AppBundle\Service\DeliveryManager;
use AppBundle\Service\RoutingInterface;
use AppBundle\Utils\OrderTimeHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Prophecy\Argument;

class DeliveryManagerTest extends KernelTestCase
{
    private $expressionLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->expressionLanguage = static::$kernel->getContainer()->get('coopcycle.expression_language');

        $this->orderTimeHelper = $this->prophesize(OrderTimeHelper::class);
        $this->routing = $this->prophesize(RoutingInterface::class);
    }

    public function testGetPrice()
    {
        $rule1 = new PricingRule();
        $rule1->setExpression('distance in 0..3000');
        $rule1->setPrice(5.99);

        $rule2 = new PricingRule();
        $rule2->setExpression('distance in 3000..5000');
        $rule2->setPrice(6.99);

        $rule3 = new PricingRule();
        $rule3->setExpression('distance in 5000..7500');
        $rule3->setPrice(8.99);

        $ruleSet = new PricingRuleSet();
        $ruleSet->setRules(new ArrayCollection([
            $rule1,
            $rule2,
            $rule3,
        ]));

        $deliveryManager = new DeliveryManager(
            $this->expressionLanguage,
            $this->routing->reveal(),
            $this->orderTimeHelper->reveal()
        );

        $delivery = new Delivery();
        $delivery->setDistance(1500);

        $this->assertEquals(5.99, $deliveryManager->getPrice($delivery, $ruleSet));
    }

    public function testCreateFromOrder()
    {
        $restaurantAddress = new Address();
        $restaurantAddressCoords = new GeoCoordinates();
        $restaurantAddress->setGeo($restaurantAddressCoords);

        $shippingAddress = new Address();
        $shippingAddressCoords = new GeoCoordinates();
        $shippingAddress->setGeo($shippingAddressCoords);

        $restaurant = new Restaurant();
        $restaurant->setAddress($restaurantAddress);

        $order = new Order();
        $order->setRestaurant($restaurant);
        // $order->addItem($this->createOrderItem(1000));
        $order->setShippingAddress($shippingAddress);

        $asap = (new \DateTime('+3 hours'))->format(\DateTime::ATOM);

        $this->orderTimeHelper
            ->getAsap($order)
            ->willReturn($asap);

        $expectedPickupBefore = new \DateTime($asap);
        $expectedPickupBefore->modify('-900 seconds');

        $this->routing
            ->getDistance($restaurantAddressCoords, $shippingAddressCoords)
            ->willReturn(1200);

        $this->routing
            ->getDuration($restaurantAddressCoords, $shippingAddressCoords)
            ->willReturn(900);

        $deliveryManager = new DeliveryManager(
            $this->expressionLanguage,
            $this->routing->reveal(),
            $this->orderTimeHelper->reveal()
        );

        $delivery = $deliveryManager->createFromOrder($order);

        $pickup = $delivery->getPickup();
        $dropoff = $delivery->getDropoff();

        $this->assertEquals(1200, $delivery->getDistance());
        $this->assertEquals($expectedPickupBefore, $pickup->getBefore());
        $this->assertEquals($restaurantAddress, $pickup->getAddress());
        $this->assertEquals($shippingAddress, $dropoff->getAddress());
    }

    public function testCreateFromOrderThrowsException()
    {
        $this->expectException(ShippingAddressMissingException::class);

        $restaurantAddress = new Address();
        $restaurantAddressCoords = new GeoCoordinates();
        $restaurantAddress->setGeo($restaurantAddressCoords);

        $shippingAddress = new Address();
        $shippingAddressCoords = new GeoCoordinates();
        $shippingAddress->setGeo($shippingAddressCoords);

        $restaurant = new Restaurant();
        $restaurant->setAddress($restaurantAddress);

        $order = new Order();
        $order->setRestaurant($restaurant);
        // The shipping address is missing
        // $order->setShippingAddress(null);

        $deliveryManager = new DeliveryManager(
            $this->expressionLanguage,
            $this->routing->reveal(),
            $this->orderTimeHelper->reveal()
        );

        $delivery = $deliveryManager->createFromOrder($order);
    }
}
