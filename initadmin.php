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

//ajouter filtre sur page option pour supprimer email obligatoire

add_event_handler('loc_begin_admin_page', 'ucf_add_popin');
function ucf_add_popin(){
  global $template;
  $template->set_prefilter('config', 'ucf_config_prefilter');
}

add_event_handler('loc_end_admin', 'ucf_add_tab_users_modal');
function ucf_add_tab_users_modal()
{
  global $template, $page;

  if ('user_list' === $page['page'])
  {
    $template->set_filename('usercustomfield', realpath(UCF_PATH . 'ucf_user_list.tpl'));
    $template->assign(array(
      'UCF_PATH' => UCF_PATH
    ));
    $template->parse('usercustomfield');
  }
}

function ucf_config_prefilter($content){
  $search = '#(<li>
        <label class="font-checkbox">
          <span class="icon-check"></span>
          <input type="checkbox" name="obligatory_user_mail_address").*Mail address is mandatory for registration\'\|translate}
        </label>
      </li>#ms';
  return preg_replace($search, '', $content);
}
