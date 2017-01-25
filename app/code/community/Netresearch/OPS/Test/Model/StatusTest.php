<?php

class Netresearch_OPS_Test_Model_StatusTest extends EcomDev_PHPUnit_Test_Case
{
    public function testIsFinal()
    {
        $status = Netresearch_OPS_Model_Status::AUTHORIZED;
        $this->assertTrue(Netresearch_OPS_Model_Status::isFinal($status));
        $status = Netresearch_OPS_Model_Status::AUTHORIZED_WAITING_EXTERNAL_RESULT;
        $this->assertFalse(Netresearch_OPS_Model_Status::isFinal($status));

    }

    public function testIsIntermediate()
    {
        $status = Netresearch_OPS_Model_Status::AUTHORIZED_WAITING_EXTERNAL_RESULT;
        $this->assertTrue(Netresearch_OPS_Model_Status::isIntermediate($status));
        $status = Netresearch_OPS_Model_Status::AUTHORIZED;
        $this->assertFalse(Netresearch_OPS_Model_Status::isIntermediate($status));
    }


    public function testIsCapture()
    {
        $captureStatus = array(
            Netresearch_OPS_Model_Status::PAYMENT_REQUESTED,
            Netresearch_OPS_Model_Status::PAYMENT_PROCESSING,
            Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN,
            Netresearch_OPS_Model_Status::PAYMENT_REFUSED,
            Netresearch_OPS_Model_Status::PAYMENT_DECLINED_BY_ACQUIRER,
            Netresearch_OPS_Model_Status::PAYMENT_PROCESSED_BY_MERCHANT,
            Netresearch_OPS_Model_Status::REFUND_REVERSED,
            Netresearch_OPS_Model_Status::PAYMENT_IN_PROGRESS
        );
        foreach ($captureStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::isCapture($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::isCapture(Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED));
    }

    public function testIsRefund()
    {
        $refundStatus = array(
            Netresearch_OPS_Model_Status::REFUNDED,
            Netresearch_OPS_Model_Status::REFUND_PENDING,
            Netresearch_OPS_Model_Status::REFUND_UNCERTAIN,
            Netresearch_OPS_Model_Status::REFUND_REFUSED,
            Netresearch_OPS_Model_Status::REFUNDED_OK,
            Netresearch_OPS_Model_Status::REFUND_PROCESSED_BY_MERCHANT,
        );
        foreach ($refundStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::isRefund($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::isCapture(Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED));
    }

    public function testIsVoid()
    {
        $voidStatus =  array(
            Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED,
            Netresearch_OPS_Model_Status::DELETION_WAITING,
            Netresearch_OPS_Model_Status::DELETION_UNCERTAIN,
            Netresearch_OPS_Model_Status::DELETION_REFUSED,
            Netresearch_OPS_Model_Status::AUTHORIZED_AND_CANCELLED_OK,
        );
        foreach ($voidStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::isVoid($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::isVoid(Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED));
    }


    public function testIsAuthorize()
    {
        $authStatus =  array(
            Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED,
            Netresearch_OPS_Model_Status::AUTHORIZED,
            Netresearch_OPS_Model_Status::AUTHORIZED_WAITING_EXTERNAL_RESULT,
            Netresearch_OPS_Model_Status::AUTHORIZATION_WAITING,
            Netresearch_OPS_Model_Status::AUTHORIZED_UNKNOWN,
            Netresearch_OPS_Model_Status::STAND_BY,
            Netresearch_OPS_Model_Status::OK_WITH_SHEDULED_PAYMENTS,
            Netresearch_OPS_Model_Status::NOT_OK_WITH_SHEDULED_PAYMENTS,
            Netresearch_OPS_Model_Status::AUTHORISATION_TO_BE_REQUESTED_MANUALLY
        );
        foreach ($authStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::isAuthorize($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::isAuthorize(Netresearch_OPS_Model_Status::REFUND_PENDING));
    }

    public function testIsWaitingStatus()
    {
        $waitingStatus = array(
            Netresearch_OPS_Model_Status::WAITING_CLIENT_PAYMENT,
            Netresearch_OPS_Model_Status::WAITING_FOR_IDENTIFICATION,
            Netresearch_OPS_Model_Status::STORED_WAITING_EXTERNAL_RESULT
        );
        foreach ($waitingStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::isSpecialStatus($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::isSpecialStatus(Netresearch_OPS_Model_Status::REFUND_PENDING));
    }


    public function testCanResendPaymentInfo()
    {
        $canResendInfoStatus = array(
            Netresearch_OPS_Model_Status::NOT_OK_WITH_SHEDULED_PAYMENTS,
            Netresearch_OPS_Model_Status::CANCELED_BY_CUSTOMER,
            Netresearch_OPS_Model_Status::AUTHORISATION_DECLINED,
            Netresearch_OPS_Model_Status::AUTHORISATION_TO_BE_REQUESTED_MANUALLY,
            Netresearch_OPS_Model_Status::PAYMENT_UNCERTAIN,
            Netresearch_OPS_Model_Status::PAYMENT_REFUSED,
        );
        foreach ($canResendInfoStatus as $status) {
            $this->assertTrue(Netresearch_OPS_Model_Status::canResendPaymentInfo($status));
        }
        $this->assertFalse(Netresearch_OPS_Model_Status::canResendPaymentInfo(Netresearch_OPS_Model_Status::REFUND_PENDING));
    }
}

