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
class Axis_Admin_Community_RatingController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('community')->__(
            'Community review ratings'
        );
        $this->render();
    }
    
    public function getListAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->json->sendSuccess(array(
            'data' => Axis::single('community/review_rating')->getList(false, false)
        ));
    }
    
    public function saveAction()
    {
        $this->_helper->layout->disableLayout();
        
        $count       = 0; 
        $dataset     = Zend_Json_Decoder::decode($this->_getParam('data'));
        $model       = Axis::model('community/review_rating');
        $modelTitle  = Axis::single('community/review_rating_title');
        $languageIds = array_keys(Axis_Collect_Language::collect());
        
        foreach ($dataset as $data) {
            $row = $model->save($data);
            //save title
            foreach ($languageIds as $languageId) {
                $rowTitle = $modelTitle->getRow($row->id, $languageId);
                $rowTitle->title = empty($data['title_' . $languageId])
                    ? $row->name : $data['title_' . $languageId];
                
                $rowTitle->save();
            }
            $count++;
        }
        
        if ($count) {
            Axis::message()->addSuccess(
                Axis::translate('community')->__(
                    "%d rating(s) was saved successfully", $count
                )
            );
        }
        $this->_helper->json->sendSuccess();
    }
    
    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        
        Axis::single('community/review_rating')
            ->remove(Zend_Json_Decoder::decode($this->_getParam('data')));
            
        $this->_helper->json->sendSuccess();
    }
}