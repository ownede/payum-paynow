<?php

namespace Ksolutions\PayumPaynow\Action\Api;

use Ksolutions\PayumPaynow\Generator\IdempotencyKey\UniqIdIdempotencyKeyGenerator;
use Ksolutions\PayumPaynow\Request\CreatePayment;
use Paynow\Exception\PaynowException;
use Paynow\Response\Payment\Authorize;
use Paynow\Service\Payment;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;

class CreatePaymentAction extends BaseApiAwareAction
{
    use ApiAwareTrait;

    /**
     * @param CreatePayment $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        // Could be switched use ->getModel() with data from ConvertPayment
        /** object(Payum\Core\Bridge\Spl\ArrayObject)[1023]
         * protected 'input' => null
         * private 'storage' (ArrayObject) =>
         * array (size=6)
         * 'amount' => int 420
         * 'currency' => string 'PLN' (length=3)
         * 'externalId' => string '250207-43014-67A5B72C02B96' (length=26)
         * 'description' => null
         * 'buyer' =>
         * array (size=1)
         * ...
         * 'status' => string 'NEW' (length=3) */

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        $this->verifyRequiredFields($payment);

        $token = $request->getToken();
        $this->setContinueUrl($token, $payment);

        $response = $this->createPayment($payment);
        $payment->setDetails(array_merge($payment->getDetails(), [
            'status' => $response->getStatus(),
            'paymentId' => $response->getPaymentId(),
            'redirectUrl' => $response->getRedirectUrl(),
        ]));

        $request->setModel($payment);

        throw new HttpRedirect(
            $response->getRedirectUrl() !== null ? $response->getRedirectUrl() : $token->getTargetUrl()
        );
    }

    private function createPayment(PaymentInterface $payment): Authorize
    {
        $generator = new UniqIdIdempotencyKeyGenerator();

        try {
            $paymentService = new Payment($this->api);

            return $paymentService->authorize(
                $this->preparePaymentRequestData($payment),
                $generator->generate($payment->getNumber())
            );
        } catch (PaynowException $e) {
            $first = true;
            $errors = '';
            foreach ($e->getErrors() as $error) {
                if (!$first) {
                    $errors .= '; ';
                }
                $errors .= $error->getMessage();
                $first = true;
            }
            throw new RuntimeException('Payment creation failed: ' . $errors, 0, $e);
        }
    }

    private function preparePaymentRequestData(PaymentInterface $payment): array
    {
        return [
            'amount' => $payment->getTotalAmount(),
            'currency' => $payment->getCurrencyCode(),
            'externalId' => $payment->getNumber(),
            'description' => $payment->getDescription(),
            'continueUrl' => $payment->getDetails()['continueUrl'],
            'buyer' => [
                'email' => $payment->getClientEmail(),
            ]
        ];
    }

    private function setContinueUrl(TokenInterface $token, PaymentInterface $payment): void
    {
        $details = $payment->getDetails();
        if (!is_array($details)) {
            $details = [];
        }
        $details['continueUrl'] = $token->getAfterUrl() ?: $token->getTargetUrl();

        $payment->setDetails($details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof CreatePayment &&
            ($request->getModel() instanceof PaymentInterface || $request->getFirstModel() instanceof PaymentInterface)
            ;
    }

    public function verifyRequiredFields(PaymentInterface $payment): void
    {
        if (empty($payment->getTotalAmount())) {
            throw new LogicException('Required field "totalAmount" is missing');
        }
        if (empty($payment->getCurrencyCode())) {
            throw new LogicException('Required field "currencyCode" is missing');
        }
        if (empty($payment->getNumber())) {
            throw new LogicException('Required field "number" is missing');
        }
        if (empty($payment->getDescription())) {
            throw new LogicException('Required field "clientEmail" is missing');
        }
        if (empty($payment->getClientEmail())) {
            throw new LogicException('Required field "clientEmail" is missing');
        }
    }
}