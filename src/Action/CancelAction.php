<?php
namespace Ksolutions\PayumPaynow\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;

class CancelAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        throw new \LogicException('Not implemented');
    }

    public function supports($request): bool
    {
        return
            $request instanceof Cancel &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
