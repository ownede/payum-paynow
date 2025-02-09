<?php
namespace Ksolutions\PayumPaynow\Action;

use Ksolutions\PayumPaynow\Enum\Currencies;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false === Currencies::isAllowed($payment->getCurrencyCode())) {
            throw new InvalidArgumentException(
                sprintf('Currency %s is not supported.', $payment->getCurrencyCode())
            );
        }

        $details['amount'] = $payment->getTotalAmount();
        $details['currency'] = $payment->getCurrencyCode();
        $details['externalId'] = $payment->getNumber();
        $details['description'] = $payment->getDescription();
        $details['buyer'] = [
            'email' => $payment->getClientEmail(),
        ];
        $details['status'] = 'NEW';

        $payment->setDetails((array) $details);
        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
            ;
    }
}
