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
add_event_handler('loc_begin_profile', 'ucfPI', 95 );

function ucfPI(){
  global $template;
  $template->set_prefilter('profile_content', 'ucfPT');
  $template->set_filename('ucf_profile_add', realpath(UCF_PATH.'ucf_profile_add.tpl'));
  $template->assign_var_from_handle('UCF_PROFILE_ADD', 'ucf_profile_add');
}

function ucfPT($content){
  $search = '/(<legend>{\'Registration).*({if \$ALLOW_USER_CUSTOMIZATION}
  <fieldset>)/is';
  return preg_replace($search, '{$UCF_PROFILE_ADD}'."\n".'{if $ALLOW_USER_CUSTOMIZATION}
  <fieldset>', $content);
 }

add_event_handler('loc_begin_profile', 'ucfinitprofil');
function ucfinitprofil(){
  global $template, $userdata, $pwg_loaded_plugins, $conf;
 if (isset($userdata['id'])){ 
	if (isset($pwg_loaded_plugins['ExtendedDescription'])){
		add_event_handler('AP_render_content', 'get_user_language_desc');
    }
  $tab_user_register=tab_user_custom_fields_register();
  
  $template->assign('UCF_USERNAME',$userdata['username']);
  $template->assign('UCF_EMAIL',$userdata['email']);
  $special_user = in_array($userdata['id'], array($conf['guest_id'], $conf['default_user_id']));
  $template->assign('SPECIAL_USER', $special_user);
  while ($info_users = pwg_db_fetch_assoc($tab_user_register)) {
	$d=data_info_user($userdata['id'],$info_users['id_ucf']);
	$row = pwg_db_fetch_assoc($d);
	$items['UCFID'] = $info_users['id_ucf'];
	$items['UCFWORDING'] = trigger_change('AP_render_content', $info_users['wording']);
	$items['UCFOBLIGATORY'] = $info_users['obligatory'];
	$items['UCFDATA'] = $row['data'] ?? '';

	$template->append('edit_users_profil', $items);
  }
 }
}

 
add_event_handler('save_profile_from_post', 'ucfPT2');
function ucfPT2(){
  global $prefixeTable,$userdata;
  $profil_base_url = PHPWG_ROOT_PATH . '/profile.php';
  foreach ($_POST['data'] AS $id_ucf => $data) {
	$q = 'SELECT 1 FROM ' . UCFD_TABLE . ' WHERE id_user=' . $userdata['id'] . ' AND id_ucf=' . $id_ucf;
	$test = pwg_query($q);
	$row = pwg_db_fetch_assoc($test);
	if (!empty($row)){
	  if ($data != ''){
		$query = 'UPDATE ' . UCFD_TABLE . ' SET data="' . $data . '" WHERE id_user=' . $userdata['id'] . ' AND id_ucf=' . $id_ucf;
		pwg_query($query);
	  }else{
		$query = 'DELETE FROM ' . UCFD_TABLE . ' WHERE id_user=' . $userdata['id'] . ' AND id_ucf=' . $id_ucf;
		pwg_query($query);
	  }
	}else if ($data != ''){
		$query = 'INSERT ' . UCFD_TABLE . '(id_user,id_ucf,data) VALUES (' . $userdata['id'] . ',' . $id_ucf . ',"' . $data . '");';
		pwg_query($query);
	}
  }
  redirect($profil_base_url);  
}

?>