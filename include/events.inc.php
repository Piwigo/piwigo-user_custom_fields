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
 * validate ucf fields before the user is created, so an invalid
 * registration never creates the account nor sends the mails
 */
function ucf_register_check($errors, $user_infos)
{
  if (isset($_POST['ucf']))
  {
    $result = ucf_validate_fields($_POST['ucf']);
    if (null !== $result)
    {
      // same key as the core password errors, displayed next to the form by standard pages
      $errors['register_form_error'] = $result['message'];
    }
  }

  return $errors;
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
      // if ucf_save_ucf return a error we need to delete user
      if (!defined('IN_ADMIN'))
      {
        include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
        delete_user($user['id']);
      }
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

