<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// +-----------------------------------------------------------------------+
// | Check Access and exit when user status is not ok                      |
// +-----------------------------------------------------------------------+
check_status(ACCESS_WEBMASTER);

global $page, $conf;

$page['tab'] = 'config';

// Create tabsheet
include_once(PHPWG_ROOT_PATH . 'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('ucf_plugin_tab');
$tabsheet->add('config', '<span class="icon-cog"></span>'.l10n('Configuration'), UCF_ADMIN . '-config');
$tabsheet->select($page['tab']);
$tabsheet->assign();

$template->assign(array(
  'UCF_PATH'=> UCF_PATH,
  'UCF_REALPATH'=> realpath(UCF_REALPATH),
  'UCF_NAME' => $conf['ucf_config']['ucf_name']
));
$template->set_filename('ucf_plugin_content', UCF_REALPATH . '/admin/template/configuration.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'ucf_plugin_content');