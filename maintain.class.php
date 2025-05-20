<?php
// +-----------------------------------------------------------------------+
// | User Custom Fields plugin for Piwigo by TEMMII                        |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2016-2022 ddtddt               http://temmii.com/piwigo/ |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

class user_custom_fields_maintain extends PluginMaintain
{
  private $default_conf = array(
    'ucf' => array(),
    );

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf;

    if (empty($conf['ucf_config']))
    {
      conf_update_param('ucf_config', $this->default_conf, true);
    }

    $this->ucf_get_old_conf_and_cleanup();
  }

  /**
   * Plugin activate
   */
  function activate($plugin_version, &$errors = array())
  {
  }

  /**
   * Plugin deactivate
   */
  function deactivate()
  {
  }

  /**
   * Plugin update
   */
  function update($old_version, $new_version, &$errors = array())
  {
    $this->install($new_version, $errors);
  }

  /**
   * Plugin uninstallation
   */
  function uninstall()
  {
    global $conf;

    if (!empty($conf['ucf_config']))
    {
      $conf['ucf_config'] = safe_unserialize($conf['ucf_config']);
      $query = '
ALTER TABLE `'. USER_INFOS_TABLE .'`';

      $column = array();
      foreach($conf['ucf_config']['ucf'] as $field)
      {
        $column[] = 'DROP `'.$field['column_name'].'`';
      }
      $query .= implode(', ', $column);
      pwg_query($query);
    }

    conf_delete_param('ucf_config');
  }

  private function ucf_get_old_conf_and_cleanup()
  {
    global $prefixeTable;

    // check if the older table exist
    $query = pwg_query('SHOW TABLES LIKE "'.$prefixeTable.'user_custom_fields";');
    if (!pwg_db_num_rows($query)) return;

    // remove username, password, mail_address, mail
    pwg_query('DELETE FROM `'. $prefixeTable . 'user_custom_fields` WHERE id_ucf IN (1, 2, 3, 4);');
   
    $query = '
SELECT *
  FROM `'.$prefixeTable.'user_custom_fields`;';
    $fields = query2array($query);
    if (!empty($fields))
    {
      $new_conf = $this->default_conf;
      $query = '
SELECT id_user, id_ucf, data
  FROM `'. $prefixeTable .'user_custom_fields_data`        
;';
      $ucf_data_old = query2array($query);

      $database_field = array();
      $column = array();
      $new_order = 1;
      
      foreach ($fields as $field)
      {
        $new_id = bin2hex(random_bytes(5));
        $column_name = 'ucf_'.$new_id;

        $new_conf['ucf'][] = array(
          'id' => $new_id,
          'wording' => $field['wording'],
          'order_ucf' => $new_order,
          'active' => 1 == $field['active'] ? true : false,
          'adminonly' => 1 == $field['adminonly'] ? true : false,
          'obligatory' => 1 == $field['obligatory'] ? true : false,
          'column_name' => $column_name
        );
        
        $database_field[$field['id_ucf']] = 'ucf_'.$new_id;
        $column[] = 'ADD COLUMN `' . $column_name . '` VARCHAR(255) DEFAULT NULL';

        $new_order++;
      }

      $ucf_data_new = array();
      foreach ($ucf_data_old as $ucf_data_old_line)
      {
        @$ucf_data_new[$ucf_data_old_line['id_user']]['user_id'] = $ucf_data_old_line['id_user'];
        $ucf_data_new[$ucf_data_old_line['id_user']][ $database_field[$ucf_data_old_line['id_ucf']] ] = $ucf_data_old_line['data'];
      }

      $query = '
ALTER TABLE `' . USER_INFOS_TABLE . '`';
      $query .= implode(', ', $column);
      pwg_query($query);

      mass_updates(
        USER_INFOS_TABLE,
        array('primary' => array('user_id'), 'update' => $database_field),
        $ucf_data_new
      );

      conf_update_param('ucf_config', $new_conf, true);
    }

    pwg_query('DROP TABLE IF EXISTS `'. $prefixeTable .'user_custom_fields`, `'. $prefixeTable .'user_custom_fields_data`;');
  }
}
?>
