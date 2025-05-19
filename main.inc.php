<?php
/*
Plugin Name: User Custom Fields
Version: 5.0.0
Description: Add User Custom Fields
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=833
Author: ddtddt
Author URI: http://temmii.com/piwigo/
Has Settings: webmaster
*/

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

if (basename(dirname(__FILE__)) != 'user_custom_fields')
{
  add_event_handler('init', 'ucf_plugin_error');
  function ucf_plugin_error()
  {
    global $page;
    $page['errors'][] = 'User Custom Fields plugin folder name is incorrect, uninstall the plugin and rename it to "user_custom_fields"';
  }
  return;
}

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+
global $prefixeTable;

define('UCF_DIR' , basename(dirname(__FILE__)));
define('UCF_PATH' , PHPWG_PLUGINS_PATH . UCF_DIR . '/');
define('UCF_REALPATH', realpath(UCF_PATH));
define('UCF_TABLE', $prefixeTable.'user_custom_fields');
define('UCFD_TABLE', $prefixeTable.'user_custom_fields_data');
define('UCF_ADMIN',get_root_url().'admin.php?page=plugin-'.UCF_DIR);

// +-----------------------------------------------------------------------+
// | Init User Custom Fields Plugin                                        |
// +-----------------------------------------------------------------------+
include_once(UCF_PATH . 'include/function.inc.php');
$ucf_events = UCF_REALPATH.'/include/events.inc.php';
$ucf_admin_events = UCF_REALPATH.'/include/admin_events.inc.php';
$ucf_ws = UCF_REALPATH.'/include/ws_functions.inc.php';

add_event_handler('init', 'ucf_plugin_init');
// add_event_handler('delete_user', 'ucf_delete_user');
add_event_handler('ws_add_methods', 'ucf_ws_add_methods', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_ws);
add_event_handler('ws_invoke_allowed', 'ucf_ws_users_setMyInfo', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_ws);
add_event_handler('ws_users_getList', 'ucf_ws_users_getList', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_ws);

add_event_handler('loc_end_admin', 'ucf_add_tab_users_modal', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_admin_events);

add_event_handler('load_profile_in_template', 'ucf_load_block_in_profile', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_events);
add_event_handler('save_profile_from_post', 'ucf_profile_save', EVENT_HANDLER_PRIORITY_NEUTRAL, $ucf_events);

function ucf_plugin_init()
{
  load_language('plugin.lang', UCF_PATH);
  global $conf, $template;
  $template->assign(
    array(
	 'UCF_PATH2'=> UCF_PATH,
	 'UCF_ADMIN2'=> UCF_ADMIN,
    )
  );
  $conf['ucf_config'] = safe_unserialize($conf['ucf_config']);
}

 //plugin on register
// if (script_basename() == 'register')
// {  
//  include_once(dirname(__FILE__).'/initregister.php');
// }
// // Plugin on profile page
// if (script_basename() == 'profile')  
// {  
//  include_once(dirname(__FILE__).'/initprofile.php');
// }

//   // Plugin for admin
// if (script_basename() == 'admin')   
// {
//   include_once(dirname(__FILE__).'/initadmin.php');
// }
?>