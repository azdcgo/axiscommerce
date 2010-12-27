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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Template_LayoutController extends Axis_Admin_Controller_Back
{
    /**
     * @var Axis_Core_Model_Template_Layout_Page
     */
    protected $_table;

    private $_templateId;

    public function init()
    {
        parent::init();
        $this->getHelper('layout')->disableLayout();
        $this->_table = Axis::single('core/template_layout_page');
        $this->_templateId = (int) $this->_getParam('tId', 0);
    }

    public function listAction()
    {
        $select = Axis::model('core/template_layout_page')->select('*')
            ->calcFoundRows()
            ->addFilters($this->_getParam('filter', array()))
            ->limit(
                $this->_getParam('limit', 25),
                $this->_getParam('start', 0)
            )
            ->order(
                $this->_getParam('sort', 'id')
                . ' '
                . $this->_getParam('dir', 'DESC')
            );

        $this->_helper->json->sendSuccess(array(
            'data'  => $select->fetchAll(),
            'count' => $select->foundRows()
        ));
    }

    public function listCollectAction()
    {
        $layouts = Axis_Collect_Layout::collect();

        $result = array();
        $i = 0;
        foreach ($layouts as $layout) {
            $result[$i]['name'] = $layout;
            $i++;
        }

        return $this->_helper->json->sendSuccess(array(
            'data' => $result
        ));
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();

        $data = Zend_Json::decode($this->_getParam('data'));

        return $this->_helper->json->sendJson(array('success' =>
            Axis::single('core/template_layout_page')->save(
                $this->_templateId, $data
            )
        ));
    }

    public function deleteAction()
    {
        $ids = Zend_Json::decode($this->_getParam('data'));

        if (!count($ids)) {
            Axis::message()->addError(
                Axis::translate('admin')->__(
                    'No data to delete'
                )
            );
            return $this->_helper->json->sendFailure();
        }
        $this->_table->delete($this->db->quoteInto('id IN(?)', $ids));

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Data was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}