# Payum Paynow.pl Gateway

This is a basic Paynow.pl payment gateway for [Payum](https://github.com/Payum/Payum).

## Installation

To install this package, you can use composer:
```bash
composer require payum/paynow ksolutionspro/payum-paynow
```

## Usage

Simply register the gateway factory and you're good to go.

### Symfony integration

If you are planning to use this package with Symfony and PayumBundle, you can use the following configuration.

1. Register the gateway factory in your `services.yaml`:

```yaml
Ksolutions\PayumPaynow\PaynowGatewayFactory:
  class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
  arguments: [ Ksolutions\PayumPaynow\PaynowGatewayFactory ]
  tags:
    - { name: payum.gateway_factory_builder, factory: paynow }
```

2. Add the following configuration to your `payum.yaml`:

```yaml
payum:
  gateways:
    paynow:
      factory: paynow
      api_key: 'your-api-key'
      signature_key: 'your-signature-key'
      sandbox: true # or false in production
```

### Payment notification

To handle payment notifications, you need to configure your notification URL in the Paynow.pl panel.

In such route you should fetch the Payment and call `Notify` action on the gateway.

```php
// logic to fetch the payment by 'paymentId' from notification body

$gateway = $payum->getGateway('paynow');
$gateway->execute(new Notify($payment));
```

You should return `202 Accepted` status code if the notification was processed successfully.

Return `400 Bad Request` if you were not able to process the notification.

## Contribution

I'm not actively developing this package, so you're welcome to contribute.

Feel free to create a pull request. Be sure to add yourself to the contributors list below.

## Contributors 

* Author: [Kacper Smółkowski](https://github.com/ownede)

## Payum Resources

* [Site](https://payum.forma-pro.com/)
* [Documentation](https://github.com/Payum/Payum/blob/master/docs/index.md#general)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)
