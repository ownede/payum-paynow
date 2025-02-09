<?php
namespace Ksolutions\PayumPaynow\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getFirstModel();
        $details = $payment->getDetails();

        if (!is_array($details)) {
            $details = [];
        }
        if (!isset($details['status'])) {
            $details['status'] = 'NEW';
        }

        switch ($details['status']) {
            case 'NEW':
                $request->markNew();
                return;
            case 'PENDING':
                $request->markPending();
                return;
            case 'CONFIRMED':
                $request->markCaptured();
                return;
            case 'ERROR':
            case 'REJECTED':
                $request->markFailed();
                return;
            case 'EXPIRED':
                $request->markExpired();
                return;
            case 'ABANDONED':
                $request->markCanceled();
                return;
        }

        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            ($request->getModel() instanceof PaymentInterface || $request->getModel() instanceof \ArrayAccess)
        ;
    }
}
