<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sales
 * @subpackage  Axis_Sales_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sales_Model_Order_Creditcard extends Axis_Db_Table
{
    protected $_name = 'sales_order_creditcard';

    /**
     * Insert or update creditcard order row
     *
     * @param Axis_CreditCard            $card
     * @param Axis_Sales_Model_Order_Row $order
     * @return boolean
     */
    public function save(Axis_CreditCard $card, Axis_Sales_Model_Order_Row $order)
    {
        $full_number = $card->getCcNumber();
        $ret = true;

        switch (Axis::config()->payment->{$order->payment_method_code}->saveCCAction) {
            case 'last_four':
                $number = str_repeat('X', (strlen($full_number) - 4)) .
                    substr($full_number, -4);
                break;
            case 'first_last_four':
                $number = substr($full_number, 0, 4) .
                    str_repeat('X', (strlen($full_number) - 8)) .
                    substr($full_number, -4);
                break;
            case 'partial_email':
                $number = substr($full_number, 0, 4) .
                    str_repeat('X', (strlen($full_number) - 8)) .
                    substr($full_number, -4);

                try {
                    $mail = new Axis_Mail();
                    $mail->setLocale(Axis::config('locale/main/language_admin'));
                    $configResult = $mail->setConfig(array(
                        'subject' => Axis::translate('sales')->__('Order #%s. Credit card number'),
                        'data'    => array(
                            'text' => Axis::translate('sales')->__(
                                'Order #%s, Credit card middle digits: %s',
                                $order->number,
                                substr($full_number, 4, (strlen($full_number) - 8))
                            )
                        ),
                        'to' => Axis_Collect_MailBoxes::getName(
                            Axis::config('sales/order/email')
                        )
                    ));
                    $mail->send();

                    if (!$configResult) {
                        $ret = false;
                    }
                } catch (Zend_Mail_Transport_Exception $e) {
                    $ret = false;
                }
                break;
            case 'complete':
                $number = $full_number;
                break;
            default:
                return true;
        }

        $crypt = Axis_Crypt::factory();
        $data = array(
            'cc_type'   => $crypt->encrypt($card->getCcType()),
            'cc_owner'  => $crypt->encrypt($card->getCcOwner()),
            'cc_number' => $crypt->encrypt($number),
            'cc_expires_year' => $crypt->encrypt($card->getCcExpiresYear()),
            'cc_expires_month' => $crypt->encrypt($card->getCcExpiresMonth()),
            'cc_cvv'    => Axis::config()->payment->{$order->payment_method_code}->saveCvv ?
                $crypt->encrypt($card->getCcCvv()) : '',
            'cc_issue_year'  => $crypt->encrypt($card->getCcIssueYear()),
            'cc_issue_month'  => $crypt->encrypt($card->getCcIssueMonth())
        );

        if (!$row = $this->find($order->id)->current()) {
            $row = $this->createRow();
            $row->order_id = $order->id;
        }
        $row->setFromArray($data);

        return $ret && $row->save();
    }
}