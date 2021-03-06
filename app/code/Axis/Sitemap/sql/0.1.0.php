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
 * @package     Axis_Sitemap
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Sitemap_Upgrade_0_1_0 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.1.0';
    protected $_info = 'install';

    public function up()
    {
        $installer = Axis::single('install/installer');

        $installer->run("

        -- DROP TABLE IF EXISTS `{$installer->getTable('sitemap_file')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sitemap_file')}` (
          `id` mediumint(8) unsigned NOT NULL auto_increment,
          `filename` varchar(225) NOT NULL default 'sitemap',
          `generated_at` date NOT NULL,
          `site_id` smallint(5) unsigned NOT NULL,
          `status` smallint(5) unsigned NOT NULL,
          `usage_at` date NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `FK_sitemap_file_site` (`site_id`),
          CONSTRAINT `FK_sitemap_file_site` FOREIGN KEY (`site_id`) REFERENCES `{$installer->getTable('core_site')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

        -- DROP TABLE IF EXISTS `{$installer->getTable('sitemap_file_engine')}`;
        CREATE TABLE IF NOT EXISTS `{$installer->getTable('sitemap_file_engine')}` (
          `sitemap_file_id` mediumint(8) unsigned NOT NULL,
          `sitemap_engine_id` smallint(5) unsigned NOT NULL default '0',
          PRIMARY KEY  USING BTREE (`sitemap_file_id`,`sitemap_engine_id`),
          KEY `FK_sitemap_to_engine` (`sitemap_engine_id`),
          CONSTRAINT `FK_sitemap_file_engine` FOREIGN KEY (`sitemap_file_id`) REFERENCES `{$installer->getTable('sitemap_file')}` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

        ");

        Axis::single('core/config_field')
            ->add('sitemap', 'Sitemap', null, null, array('translation_module' => 'Axis_Sitemap'))
            ->add('sitemap/main/enabled', 'Sitemap/General/Enabled', '0', 'bool', 'Enabled')
            ->add('sitemap/main/startTime', 'Start Time', '', 'string', 'Start Time')
            ->add('sitemap/main/frequency', 'Frequency', 'daily', 'select', 'Frequency', array('config_options' => 'always,hourly,daily,weekly,monthly,yearly,never'))

            ->add('sitemap/main/googlePingUrl', 'Google Ping Url', 'http://www.google.com/webmasters/sitemaps/ping?sitemap=')
            ->add('sitemap/main/yahooPingUrl', 'Yahoo Ping Url', 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=')
            ->add('sitemap/main/askPingUrl', 'Ask Ping Url', 'http://submissions.ask.com/ping?sitemap=')
            ->add('sitemap/main/msnPingUrl', 'Msn Ping Url', 'http://www.moreover.com/ping?u=')

            ->add('sitemap/categories/priority', 'Sitemap/Categories Options/Priority', '0.8', 'string', 'The priority of this URL relative to other URLs on your site.Valid values range from 0.0 to 1.0')
            ->add('sitemap/categories/frequency', 'Frequency', 'daily', 'select', 'Frequency', array('config_options' => 'always,hourly,daily,weekly,monthly,yearly,never'))
            ->add('sitemap/products/priority', 'Sitemap/Products Options/Priority', '0.8', 'string', 'The priority of this URL relative to other URLs on your site.Valid values range from 0.0 to 1.0')
            ->add('sitemap/products/frequency', 'Frequency', 'daily', 'select', 'Frequency', array('config_options' => 'always,hourly,daily,weekly,monthly,yearly,never'))
            ->add('sitemap/cms/priority', 'Sitemap/CMS Pages Options/Priority', '0.5', 'string', 'The priority of this URL relative to other URLs on your site.Valid values range from 0.0 to 1.0')
            ->add('sitemap/cms/frequency', 'Frequency', 'daily', 'select', 'Frequency', array('config_options' => 'always,hourly,daily,weekly,monthly,yearly,never'))
            ->add('sitemap/cms/showPages', 'Show pages', '1', 'bool', 'Show pages on sitemap page');


        Axis::single('admin/menu')
            ->add('CMS', null, 90, 'Axis_Cms')
            ->add('CMS->Sitemap', 'sitemap_index', 50, 'Axis_Sitemap');

        Axis::single('admin/acl_resource')
            ->add('admin/sitemap', 'Sitemap All')
            ->add('admin/sitemap_index', 'Sitemap')
            ->add("admin/sitemap_index/index")
            ->add("admin/sitemap_index/list")
            ->add("admin/sitemap_index/ping")
            ->add("admin/sitemap_index/quick-save")
            ->add("admin/sitemap_index/remove")
            ->add("admin/sitemap_index/save");

        Axis::single('core/page')
            ->add('sitemap/*/*')
            ->add('sitemap/index/*')
            ->add('sitemap/index/index')
            ->add('sitemap/index/get-all-categories')
            ->add('sitemap/index/get-all-products')
            ->add('sitemap/index/get-all-pages');
    }

    public function down()
    {
        $installer = Axis::single('install/installer');

        $installer->run("
            DROP TABLE IF EXISTS `{$installer->getTable('sitemap_file')}`;
            DROP TABLE IF EXISTS `{$installer->getTable('sitemap_file_engine')}`;
        ");

        Axis::single('core/config_field')->remove('sitemap');
        Axis::single('core/config_value')->remove('sitemap');

        Axis::single('admin/menu')->remove('CMS->Sitemap');

        Axis::single('admin/acl_resource')->remove('admin/sitemap');

        Axis::single('core/page')->remove('sitemap/*/*')
            ->remove('sitemap/index/*')
            ->remove('sitemap/index/index')
            ->remove('sitemap/index/get-all-categories')
            ->remove('sitemap/index/get-all-products')
            ->remove('sitemap/index/get-all-pages');
    }
}