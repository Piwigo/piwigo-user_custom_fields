<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

/**
 * add template for a tab in users modal
 */
function ucf_add_tab_users_modal()
{
  global $page, $template, $conf;

  if ('user_list' === $page['page'])
  {

    $fields = ucf_get_fields(true);
    $template->set_filename('ucf_user_list', UCF_REALPATH.'/admin/template/ucf_user_list.tpl');
    $template->assign(array(
      'UCF_PATH' => UCF_PATH,
      'UCF_FIELDS' => $fields,
      'UCF_NAME' => $conf['ucf_config']['ucf_name'],
    ));
    $template->parse('ucf_user_list');
  }
}