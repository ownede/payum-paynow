<?php
namespace Ksolutions\PayumPaynow;

use Ksolutions\PayumPaynow\Action\Api\CreatePaymentAction;
use Ksolutions\PayumPaynow\Action\AuthorizeAction;
use Ksolutions\PayumPaynow\Action\CancelAction;
use Ksolutions\PayumPaynow\Action\CaptureAction;
use Ksolutions\PayumPaynow\Action\ConvertPaymentAction;
use Ksolutions\PayumPaynow\Action\NotifyAction;
use Ksolutions\PayumPaynow\Action\RefundAction;
use Ksolutions\PayumPaynow\Action\StatusAction;
use Paynow\Client;
use Paynow\Environment;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PaynowGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'paynow',
            'payum.factory_title' => 'Paynow',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.create_payment' => new CreatePaymentAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'api_key',
                'signature_key',
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Client(
                    $config['api_key'],
                    $config['signature_key'],
                    $config['sandbox'] ? Environment::SANDBOX : Environment::PRODUCTION,
                );
            };
        }
    }
}
