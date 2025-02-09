<?php
namespace Ksolutions\PayumPaynow\Action;

use JsonException;
use Paynow\Exception\SignatureVerificationException;
use Paynow\Notification;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

class NotifyAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $headers = getallheaders();

        try {
            new Notification('TestSignatureKey', $httpRequest->content, $headers);
        } catch (SignatureVerificationException $exception) {
            throw new HttpResponse('', 400);
        }

        try {
            // ['paymentId' => '...', 'externalId' => '...', 'status' => '...', 'modifiedAt' => '...']
            $notificationData = json_decode(
                json: $httpRequest->content,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            throw new RequestNotSupportedException('Invalid JSON data');
        }

        $model['paymentId'] = $notificationData['paymentId'];
        $model['status'] = $notificationData['status'];

        throw new HttpResponse('', 202);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
