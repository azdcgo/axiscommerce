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
 * @package     Axis_Collect
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Collect
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Collect_Layout implements Axis_Collect_Interface
{
    /**
     *
     * @static
     * @var array
     */
    protected static $_collection;

    /**
     *
     * @static
     * @return array
     */
    public static function collect()
    {
        if (null === self::$_collection) {
            $themes = Axis_Collect_Theme::collect();
            $layouts = array();
            $designPath = Axis::config('system/path') . '/app/design/front';
            foreach ($themes as $theme) {
                $path = $designPath . '/' . $theme . '/layouts';
                if (!file_exists($path)) {
                    continue;
                }
                $dir = opendir($path);
                while (($file = readdir($dir))) {
                    if (is_dir($path . '/' . $file)
                        || substr($file, 0, 7) != 'layout_') {

                        continue;
                    }
                    $layout = substr($file, 0, -6);
                    if (isset($layouts[$layout])) {
                        $layouts[$layout]['themes'][] = $theme;
                        continue;
                    }
                    $layouts[$layout] = array(
                        'name' => $layout,
                        'themes' => array($theme)
                    );
                }
            }
            $collection = array();
            foreach ($layouts as $key => $layout) {
                $collection[$key] = $layout['name'] 
                    . ' (' . implode(', ', $layout['themes']) . ')';
            }

            self::$_collection = $collection;
        }
        return self::$_collection;
    }

    /**
     *
     * @static
     * @param string $id
     * @return string
     */
    public static function getName($id)
    {
        if (null === self::$_collection) {
            self::$_collection = self::collect();
        }
        return isset(self::$_collection[$id]) ? self::$_collection[$id] : false;
    }
}