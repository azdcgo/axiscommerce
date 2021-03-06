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
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Contacts
 * @subpackage  Axis_Contacts_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Contacts_Model_Message_Select extends Axis_Db_Table_Select
{
    /**
     *
     * @param int $siteId
     * @return Axis_Contacts_Model_Message_Select
     */
    public function addSiteFilter($siteId)
    {
        if (!empty($siteId)) {
            $this->where('site_id = ?', $siteId);
        }
        return $this;
    }

    /**
     *
     * @param int $departamentId
     * @return  Axis_Contacts_Model_Message_Select
     */
    public function addDepartamentFilter($departamentId)
    {
        if ($departamentId) {
            $this->where('department_id = ?', $departamentId);
        }
        return $this;

    }

    /**
     *
     * @return  Axis_Contacts_Model_Message_Select
     */
    public function addDepartmentName()
    {
        return $this->join('contacts_department',
            'cd.id = cm.department_id',
            array('department_name' => 'name')
        );
    }
}