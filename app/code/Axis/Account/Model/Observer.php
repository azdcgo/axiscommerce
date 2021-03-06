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
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Account
 * @subpackage  Axis_Account_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Account_Model_Observer
{
    /**
     * Send notification emails to registered cutomer, store owner
     *
     * @param array $data
     * Array(
     *  'customer' => Axis_Account_Model_Customer_Row
     *  'password' => string
     * )
     * @return void
     */
    public function notifyCustomerRegistration($data)
    {
        try {
            $mail = new Axis_Mail();
            $mail->setLocale($data['customer']->locale);
            $configResult = $mail->setConfig(array(
                'event' => 'account_new-customer',
                'subject' => Axis::translate('account')->__(
                    "Welcome, %s %s",
                    $data['customer']->firstname,
                    $data['customer']->lastname
                ),
                'data' => array(
                    'customer' => $data['customer'],
                    'password' => $data['password']
                ),
                'to' => $data['customer']->email
            ));
            $mail->send();
//            if ($configResult) {
//                Axis::message()->addSuccess(
//                    Axis::translate('core')->__('Mail was sended successfully')
//                );
//            }
        } catch (Zend_Mail_Transport_Exception $e) {
            Axis::message()->addError(
                Axis::translate('core')->__('Mail sending was failed.')
            );
        }

        try {
            $mailNotice = new Axis_Mail();
            $mailNotice->setLocale(Axis::config('locale/main/language_admin'));
            $mailNotice->setConfig(array(
                'event'   => 'account_new-owner',
                'subject' => Axis::translate('account')->__('New Account Created'),
                'data'    => array(
                    'customer' => $data['customer']
                ),
                'to' => Axis_Collect_MailBoxes::getName(
                    Axis::config('core/company/administratorEmail')
                )
            ));
            $mailNotice->send();
        } catch (Zend_Mail_Transport_Exception $e) {
        }
    }

    /**
     *
     * @param Axis_Account_Box_Navigation $box
     */
    public function prepareAccountNavigationBox(Axis_Account_Box_Navigation $box)
    {
        $view = $box->getView();
        $box->addItem($view->href('account', true), 'My Account', 'link-account', 10)
            ->addItem($view->href('account/info/change', true), 'Change Info', 'link-change-info', 20)
            ->addItem($view->href('account/address-book', true), 'Address Book', 'link-address-book', 30)
            ->addItem($view->href('account/order', true), 'My Orders', 'link-orders', 40)
            ->addItem($view->href('account/wishlist', true), 'My Wishlist', 'link-wishlist', 50)
            ->addItem($view->href('account/auth/logout', true), 'Logout', 'link-logout', 100);
    }
}