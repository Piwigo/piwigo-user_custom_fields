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

//add prefiter

add_event_handler('loc_end_page_header', 'ucfI', 55 );
function ucfI(){
  global $template;
  $template->set_prefilter('register', 'ucfIT');
  $template->set_filename('ucf_register_add', realpath(UCF_PATH.'ucf_register_add.tpl'));
  $template->assign_var_from_handle('UCF_REGISTER_ADD', 'ucf_register_add');
}

function ucfIT($content){
  $search = '/(<fieldset>).*(<\/fieldset>)/is';
  return preg_replace($search, '{$UCF_REGISTER_ADD}', $content);
}

add_event_handler('loc_end_page_header', 'ucfinit');
function ucfinit(){
  global $template, $pwg_loaded_plugins;
    if (isset($pwg_loaded_plugins['ExtendedDescription'])){
		add_event_handler('AP_render_content', 'get_user_language_desc');
    }
  $tab_user_register=tab_user_custom_fields_register();
  while ($info_users = pwg_db_fetch_assoc($tab_user_register)) {
	$items = array(
		'UCFID' => $info_users['id_ucf'],
		'UCFWORDING' => trigger_change('AP_render_content', $info_users['wording']),
		'UCFOBLIGATORY' => $info_users['obligatory'],
	);
	$template->append('add_uers_register', $items);
  }
}


add_event_handler('register_user', 'ucfT');
function ucfT($register_user)
{
  if (count($_POST['data']) == 0)
  {
    return;
  }

  $inserts = array();

  foreach ($_POST['data'] AS $id_ucf => $data)
  {
    if (!empty($data))
    {
      $inserts[] = array(
        'id_user' => $register_user['id'],
        'id_ucf' => $id_ucf,
        'data' => $data,
      );
    }
  }

  if (count($inserts) > 0)
  {
    mass_inserts(UCFD_TABLE, array_keys($inserts[0]), $inserts);
  }
}

?>