<?php 
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

/**
 * `User Custom Fields` : Delete id_user in UCFD_TABLE
 */
function ucf_delete_user($user_id)
{
  if (!preg_match('/^\d+$/', $user_id)) return;
  $query = '
DELETE
  FROM '.UCFD_TABLE.'
  WHERE id_user = '.$user_id.'
;';
  pwg_query($query);
}

/**
 * `User Custom Fields` : get Field by ID
 */
function ucf_get_field_by_id($id)
{
  global $conf;
  
  $ucf = $conf['ucf_config']['ucf'];
  $current_index_ucf = array_search($id, array_column($ucf, 'id'));
  if (false === $current_index_ucf)
  {
    return false;
  }
  return $ucf[ $current_index_ucf ];
}

/**
 * `User Custom Fields` : get all fields
 */
function ucf_get_fields($active=false, $without_admin=false)
{
  global $conf;
  $ucf = $conf['ucf_config']['ucf'];

  if ($active)
  {
    $ucf = array_filter($ucf, function($field) {
      return $field['active'] === true; 
    });
  }

  if ($without_admin)
  {
    $ucf = array_filter($ucf, function($field) {
      return $field['adminonly'] === false; 
    });
  }

  usort($ucf, function($a, $b) {
    return $a['order_ucf'] <=> $b['order_ucf'];
  });
  return $ucf;
}

/**
 * `User Custom Fields` : get user data and field
 * 
 * Only active fields
 * 
 * Without admin fields
 */
function ucf_get_userdata_and_fields($user_id)
{
  global $user;
  if (!preg_match('/^\d+$/', $user_id)) return false;

  $fields = ucf_get_fields(true, true);
  foreach ($fields as &$field)
  {
    $field['data'] = $user[ $field['column_name'] ];
  }

  return $fields;
}

/**
 * `User Custom Fields` : sava user data from profile
 */
function ucf_save_profile($ucf_post)
{
  global $user, $conf;

  $ucf_post['user_id'] = $ucf_post['user_id'] ?? $user['id'];
  if (!is_admin() AND $user['id'] != $ucf_post['user_id'])
  {
    return array(
      'error' => 401,
      'message' => 'Acces Denied'
    );
  }

  if (is_a_guest())
  {
    return array(
      'error' => 401,
      'message' => 'Acces Denied'
    );
  }

  $ucf = $conf['ucf_config']['ucf'];
  $ucf_data_new = array();
  $database_field = array();
  foreach ($ucf_post['ucf'] as $field)
  {
    if (!isset($field['ucf_id']) OR !isset($field['data']))
    {
      return array(
        'error' => WS_ERR_INVALID_PARAM,
        'message' => 'Missing ucf_id or data params'
      );
    }

    if (strlen($field['data']) > 255)
    {
      return array(
        'error' => WS_ERR_INVALID_PARAM,
        'message' => 'Data field for `'.$field['ucf_id'].'` is to long (max 255 character)'
      );
    }

    $current_index_ucf = array_search($field['ucf_id'], array_column($ucf, 'id'));
    if (false === $current_index_ucf)
    {
      return array(
        'error' => 401,
        'message' => 'Field not found!'
      );
    }

    if ($ucf[ $current_index_ucf ][ 'obligatory' ])
    {
      if (empty($field['data']) OR null == $field['data'])
      {
        return array(
          'error' => WS_ERR_INVALID_PARAM,
          'message' => '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is required'
        );
      }
    }

    if (!$ucf[ $current_index_ucf ][ 'active' ])
    {
      return array(
        'error' => WS_ERR_INVALID_PARAM,
        'message' => 'Cannot update unactive field'
      );
    }

    if (!is_admin() and $ucf[ $current_index_ucf ][ 'adminonly' ])
    {
      return array(
        'error' => WS_ERR_INVALID_PARAM,
        'message' => '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is onlyadmin field'
      );
    }

    $current_column_name = $ucf[ $current_index_ucf ][ 'column_name' ];

    $field['data'] = pwg_db_real_escape_string($field['data']);

    @$ucf_data_new[$ucf_post['user_id']]['user_id'] = $ucf_post['user_id'];
    $ucf_data_new[$ucf_post['user_id']][$current_column_name] = pwg_db_real_escape_string($field['data']);
    $database_field[$current_column_name] = 1;
  }

  mass_updates(
    USER_INFOS_TABLE,
    array('primary' => array('user_id'), 'update' => array_keys($database_field)),
    $ucf_data_new
  );
  return true;
}