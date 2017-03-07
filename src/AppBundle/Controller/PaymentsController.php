<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;

/**
 * Class PaymentsController
 * @package AppBundle\Controller
 */
class PaymentsController extends Controller
{
    /**
     * @Route("/customer", name="customer")
     */
    public function customerAction(Request $request)
    {
        Stripe::setApiKey('sk_test_GMhA4yYfqLhDfVhgEysvOV7D');

        $customer = Customer::create([
            'email' => $request->get('email'),
            'source' => [
                'object' => 'card',
                'exp_month' => $request->get('exp_month'),
                'exp_year'  => $request->get('exp_year'),
                'number'    => str_replace(' ', '', $request->get('number')),
                'cvc'       => $request->get('cvc')
            ]
        ]);

        Subscription::create([
            'customer' => $customer['id'],
            'plan' => 1
        ]);

        return new JsonResponse($customer);
    }
}
