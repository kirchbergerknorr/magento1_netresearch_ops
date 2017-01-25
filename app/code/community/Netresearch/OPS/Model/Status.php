<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2015 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Status.php
 *
 * @category Payment
 * @package  Netresearch_OPS
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
?>
<?php

/**
 * OPS response status
 *
 * Statuses in 1 digit are 'normal' statuses:
 *
 * 0 or 1 means the payment entry was not completed either because it is still underway or because interrupted or because
 *   a validation error prevented from confirming. If the cause is a validation error,
 *   an additional error code (*) (NCERROR) identifies the error.
 *
 * 2 means the acquirer did not authorise the payment.
 *
 * 5 means the acquirer authorised the payment.
 *
 * 9 means the payment was captured.
 *
 * Statuses in 2 digits corresponds either to  'intermediary' situations or to abnormal events. When the second digit  is:
 *
 * 1, this means the payment processing is on hold.
 *
 * 2, this means an error occurred during the communication with the acquirer. The result is therefore not determined.
 *    You must therefore call the acquirer's helpdesk to find out the actual result of this transaction.
 *
 * 3, this means the payment processing (capture or cancellation) was refused by the acquirer whilst the payment had been authorised beforehand.
 *    It can be due to a technical error or to the expiration of the autorisation. You must therefore call the acquirer's helpdesk to find out the actual result of this transaction.
 *
 */
class Netresearch_OPS_Model_Status
{

    const INVALID_INCOMPLETE = 0;
    const CANCELED_BY_CUSTOMER = 1;
    const AUTHORISATION_DECLINED = 2;

    const ORDER_STORED = 4;
    const STORED_WAITING_EXTERNAL_RESULT = 40;
    const WAITING_CLIENT_PAYMENT = 41;
    const WAITING_FOR_IDENTIFICATION = 46;

    const AUTHORIZED = 5;
    const AUTHORIZED_WAITING_EXTERNAL_RESULT = 50;
    const AUTHORIZATION_WAITING = 51;
    const AUTHORIZED_UNKNOWN = 52;
    const STAND_BY = 55;
    const OK_WITH_SHEDULED_PAYMENTS = 56;
    const NOT_OK_WITH_SHEDULED_PAYMENTS = 57;
    const AUTHORISATION_TO_BE_REQUESTED_MANUALLY = 59;

    const AUTHORIZED_AND_CANCELLED = 6;
    const DELETION_WAITING = 61;
    const DELETION_UNCERTAIN = 62;
    const DELETION_REFUSED = 63;
    const AUTHORIZED_AND_CANCELLED_OK = 64;

    const PAYMENT_DELETED = 7;
    const PAYMENT_DELETION_PENDING = 71;
    const PAYMENT_DELETION_UNCERTAIN = 72;
    const PAYMENT_DELETION_REFUSED = 73;
    const PAYMENT_DELETION_OK = 74;
    const DELETION_HANDLED_BY_MERCHANT = 75;

    const REFUNDED = 8;
    const REFUND_PENDING = 81;
    const REFUND_UNCERTAIN = 82;
    const REFUND_REFUSED = 83;
    const REFUNDED_OK = 84;
    const REFUND_PROCESSED_BY_MERCHANT = 85;

    const PAYMENT_REQUESTED = 9;
    const PAYMENT_PROCESSING = 91;
    const PAYMENT_UNCERTAIN = 92;
    const PAYMENT_REFUSED = 93;
    const PAYMENT_DECLINED_BY_ACQUIRER = 94;
    const PAYMENT_PROCESSED_BY_MERCHANT = 95;
    const REFUND_REVERSED = 96;
    const PAYMENT_IN_PROGRESS = 99;

    /**
     * Returns if the given status is a final status (single digit status)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isFinal($status)
    {
        return in_array(
            $status, array(
                self::INVALID_INCOMPLETE,
                self::CANCELED_BY_CUSTOMER,
                self::AUTHORISATION_DECLINED,
                self::ORDER_STORED,
                self::AUTHORIZED,
                self::AUTHORIZED_AND_CANCELLED,
                self::PAYMENT_DELETED,
                self::DELETION_HANDLED_BY_MERCHANT,
                self::REFUNDED,
                self::REFUND_PROCESSED_BY_MERCHANT,
                self::PAYMENT_REQUESTED,
                self::PAYMENT_PROCESSED_BY_MERCHANT
            )
        );
    }

    /**
     * Checks if the given status is an intermediate one (not single digit)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isIntermediate($status)
    {
        return !self::isFinal($status);
    }

    /**
     * Checks if the given state belongs to the capture status group (9 and 9x)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isCapture($status)
    {
        return in_array(
            $status,
            array(
                self::PAYMENT_REQUESTED,
                self::PAYMENT_PROCESSING,
                self::PAYMENT_UNCERTAIN,
                self::PAYMENT_REFUSED,
                self::PAYMENT_DECLINED_BY_ACQUIRER,
                self::PAYMENT_PROCESSED_BY_MERCHANT,
                self::REFUND_REVERSED,
                self::PAYMENT_IN_PROGRESS
            )
        );
    }

    /**
     * Checks if the given status belongs to refund status group (8 and 8x)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isRefund($status)
    {
        return in_array(
            $status,
            array(
                self::REFUNDED,
                self::REFUND_PENDING,
                self::REFUND_UNCERTAIN,
                self::REFUND_REFUSED,
                self::REFUNDED_OK,
                self::REFUND_PROCESSED_BY_MERCHANT,
            )
        );
    }

    /**
     * Checks if the given status belongs to void/delete status group (6 and 6x)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isVoid($status)
    {
        return in_array(
            $status,
            array(
                self::PAYMENT_DELETED,
                self::AUTHORIZED_AND_CANCELLED,
                self::DELETION_WAITING,
                self::DELETION_UNCERTAIN,
                self::DELETION_REFUSED,
                self::AUTHORIZED_AND_CANCELLED_OK,
            )
        );
    }

    /**
     * Checks if the given status belongs to authorize status group
     *
     * @param int $status
     *
     * @return bool
     */
    static function isAuthorize($status)
    {
        return in_array(
            $status,
            array(
                self::AUTHORISATION_DECLINED,
                self::AUTHORIZED,
                self::AUTHORIZED_WAITING_EXTERNAL_RESULT,
                self::AUTHORIZATION_WAITING,
                self::AUTHORIZED_UNKNOWN,
                self::STAND_BY,
                self::OK_WITH_SHEDULED_PAYMENTS,
                self::NOT_OK_WITH_SHEDULED_PAYMENTS,
                self::AUTHORISATION_TO_BE_REQUESTED_MANUALLY,
                self::CANCELED_BY_CUSTOMER
            )
        );
    }

    /**
     * Checks if given Status is a special status (waiting for client payment, waiting for authentification, invalid)
     *
     * @param int $status
     *
     * @return bool
     */
    static function isSpecialStatus($status)
    {
        return in_array(
            $status,
            array(
                self::WAITING_CLIENT_PAYMENT,
                self::WAITING_FOR_IDENTIFICATION,
                self::STORED_WAITING_EXTERNAL_RESULT,
                self::INVALID_INCOMPLETE,
            )
        );
    }

    /**
     * check if for a given status payment info can be resend
     *
     * @param $status
     *
     * @return bool
     */
    static function canResendPaymentInfo($status)
    {
        return in_array(
            $status,
            array(
                self::NOT_OK_WITH_SHEDULED_PAYMENTS,
                self::CANCELED_BY_CUSTOMER,
                self::AUTHORISATION_DECLINED,
                self::AUTHORISATION_TO_BE_REQUESTED_MANUALLY,
                self::PAYMENT_UNCERTAIN,
                self::PAYMENT_REFUSED,
                self::INVALID_INCOMPLETE
            )
        );
    }

    /**
     * check if payment with given status can be voided
     *
     * @param $status
     *
     * @return bool
     */
    static function canVoidTransaction($status)
    {
        return in_array(
            $status,
            array(
                self::AUTHORIZED,
                self::REFUNDED
            )
        );
    }
}