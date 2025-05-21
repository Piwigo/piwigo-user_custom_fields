<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

/**
 * save ucf fields in profile page 
 */
function ucf_profile_save($user_id)
{
  global $page;
  if (isset($_POST['ucf']))
  {
    $_POST['user_id'] = $user_id;
    $result = ucf_save_ucf($_POST);
    if (isset($result['error']))
    {
      $page['errors'][] = $result['message'];
    }
  }
}

/**
 * save ucf fields in register page 
 */
function ucf_register($user)
{
  global $page;
  if (isset($_POST['ucf']))
  {
    $_POST['user_id'] = $user['id'];
    $result = ucf_save_ucf($_POST, true);
    if (isset($result['error']))
    {
      $page['errors']['register_page_error'] = $result['message'];
    }
  }
}

/**
 * load ucf fields in profile/register page 
 */
function ucf_add_fields_in_template()
{
  global $template, $user, $conf;

  $ucf_fields = ucf_get_userdata_and_fields($user['id']);
  if (0 !== count($ucf_fields))
  {
    $template->set_filename('ucf_fields', realpath(UCF_PATH . 'template/ucf_fields.tpl'));
    $template->assign(array(
      'UCF_PATH' => UCF_PATH,
      'UCF_FIELDS'=> $ucf_fields,
      'USE_STANDARD_PAGE' => $conf['use_standard_pages']
    ));
    $template->parse('ucf_fields');
  }
}

