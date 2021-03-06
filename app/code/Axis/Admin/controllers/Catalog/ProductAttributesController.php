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
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Catalog_ProductAttributesController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('catalog')->__(
            'Product Attributes'
        );
        $this->render();
    }

    public function listAction()
    {
        $order = $this->_getParam('sort', 'id') . ' '
               . $this->_getParam('dir', 'ASC');
        $start = (int) $this->_getParam('start', 0);
        $limit = (int) $this->_getParam('limit', 20);

        $select = Axis::model('catalog/product_option')
            ->select('*')
            ->calcFoundRows()
            ->order($order)
            ->limit($limit, $start)
            ->addFilters($this->_getParam('filter', array()))
            ->addNameAndDescription(Axis_Locale::getLanguageId());

        return $this->_helper->json
            ->setData($select->fetchAll())
            ->setCount($select->count())
            ->sendSuccess();
    }

    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        $data = $this->_getParam('option');
        $row = Axis::single('catalog/product_option')->save($data);
        
        /* saving option text */
        $modelDescription = Axis::model('catalog/product_option_text'); 
        foreach (array_keys(Axis_Collect_Language::collect()) as $languageId) {
            $rowText = $modelDescription->find($row->id, $languageId)
                ->current();
            if (!$rowText) {
                $rowText = $modelDescription->createRow();
                $rowText->option_id = $row->id;
                $rowText->language_id = $languageId;
            }
            $rowText->setFromArray($data['text'][$languageId]);
            $rowText->save();
        }
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Option was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function getDataAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id', false);

        $data = array();
        $row = Axis::single('catalog/product_option')->find($id)->current();
        
        if ($row) {
            $data = $row->toArray();
            $rowset = $row->findDependentRowset('Axis_Catalog_Model_Product_Option_Text');
            foreach ($rowset as $rowText) {
                $data['text']['lang_' . $rowText->language_id] = array(
                    'name'        => $rowText->name,
                    'description' => $rowText->description
                );
            }
        }

        $this->_helper->json->setData($data)
            ->sendSuccess();
    }

    public function deleteAction()
    {
        Axis::single('catalog/product_option')->delete(
            $this->db->quoteInto('id IN(?)',
            Zend_Json_Decoder::decode($this->_getParam('data'))
        ));
        Axis::message()->addSuccess(
            Axis::translate('catalog')->__(
                'Option was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

}