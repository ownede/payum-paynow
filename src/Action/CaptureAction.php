<?php
namespace Ksolutions\PayumPaynow\Action;

use Ksolutions\PayumPaynow\Request\CreatePayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $createPayment = new CreatePayment($request->getToken());
        $createPayment->setModel($request->getFirstModel());

        $this->gateway->execute($createPayment);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            ($request->getModel() instanceof PaymentInterface || $request->getModel() instanceof \ArrayAccess)
        ;
    }
}
