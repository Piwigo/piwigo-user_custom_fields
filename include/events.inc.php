<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

/**
 * add block in profile page 
 */
function ucf_load_block_in_profile()
{
  global $template, $user, $conf;

  $block = array(
    'name' => 'User custom fields',
    'desc' => '',
    'template' => 'plugins/' . UCF_DIR . '/template/ucf_profile_block.tpl',
    'standard_show_save' => true
  );

  $template->assign(array(
    'UCF_FIELDS'=> ucf_get_userdata_and_fields($user['id'])
  ));
  $template->append('PLUGINS_PROFILE', $block);
}

/**
 * save block in profile page 
 */
function ucf_profile_save($user_id)
{
  global $page;
  if (isset($_POST['ucf']))
  {
    $_POST['user_id'] = $user_id;
    $result = ucf_save_profile($_POST);
    if (isset($result['error']))
    {
      $page['errors'][] = $result['message'];
    }
  }
}