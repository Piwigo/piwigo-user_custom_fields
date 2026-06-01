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
function ucf_save_ucf($ucf_post, $from_register=false)
{
  global $user, $conf;

  $ucf_post['user_id'] = $ucf_post['user_id'] ?? $user['id'];
  if (!$from_register AND !is_admin() AND $user['id'] != $ucf_post['user_id'])
  {
    return array(
      'error' => 401,
      'message' => 'Acces Denied'
    );
  }

  if (is_a_guest($ucf_post['user_id']))
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
        'error' => 1003,
        'message' => 'Missing ucf_id or data params'
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

    $current_type = $ucf[ $current_index_ucf ][ 'type' ];
    $ucf_validation = ucf_validate_type($ucf[ $current_index_ucf ], $field);
    if (null !== $ucf_validation)
    {
      return $ucf_validation; // error
    }

    if ($ucf[ $current_index_ucf ][ 'obligatory' ])
    {
      // for checkbox, "obligatory" means must be checked (e.g. accept terms/GDPR)
      $is_empty = 'checkbox' === $current_type
        ? 'true' !== $field['data']
        : (empty($field['data']) || null === $field['data']);

      if ($is_empty)
      {
        return array(
          'error' => 1003,
          'message' => '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is required'
        );
      }
    }

    if (!$ucf[ $current_index_ucf ][ 'active' ])
    {
      return array(
        'error' => 1003,
        'message' => 'Cannot update unactive field'
      );
    }

    if (!is_admin() and $ucf[ $current_index_ucf ][ 'adminonly' ])
    {
      return array(
        'error' => 1003,
        'message' => '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is onlyadmin field'
      );
    }

    $current_column_name = $ucf[ $current_index_ucf ][ 'column_name' ];

    $field['data'] = pwg_db_real_escape_string($field['data']);

    @$ucf_data_new[$ucf_post['user_id']]['user_id'] = $ucf_post['user_id'];
    $ucf_data_new[$ucf_post['user_id']][$current_column_name] = $field['data'];
    $database_field[$current_column_name] = 1;
  }

  mass_updates(
    USER_INFOS_TABLE,
    array('primary' => array('user_id'), 'update' => array_keys($database_field)),
    $ucf_data_new
  );
  return true;
}

function ucf_get_column_type($type)
{
  $column_type = 'VARCHAR(255) DEFAULT NULL';
  switch ($type) {
    case 'textarea':
      $column_type = 'TEXT DEFAULT NULL';
      break;

    case 'checkbox':
      $column_type = 'enum(\'true\',\'false\') default \'false\'';
      break;

    case 'date':
      $column_type = 'DATE DEFAULT NULL';
      break;
    
    default:
      $column_type = 'VARCHAR(255) DEFAULT NULL';
      break;
  }

  return $column_type;
}

function ucf_validate_type($ucf_field, $field)
{
  $error = null;
  $type = $ucf_field['type'];
  $wording = $ucf_field['wording'];

  if (!isset($field['data']) || '' === $field['data'])
  {
    return null;
  }

  switch ($type) {
    case 'text':
      if (strlen($field['data']) > 255)
      {
        $error = array(
          'error' => 1003,
          'message' => 'Data field for `'.$wording.'` is to long (max 255 character)'
        );
      }
      break;

    case 'textarea':
      break;

    case 'checkbox':
      if ( ($value = filter_var($field['data'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) === null )
      {
        $error = array(
          'error' => 1003,
          'message' => '`'.$wording.'` must only contain booleans'
        );
      }
      break;

    case 'date':
      $date = DateTime::createFromFormat('Y-m-d', $field['data']);
      if (!$date || $date->format('Y-m-d') !== $field['data'])
      {
        $error = array(
          'error' => 1003,
          'message' => '`'.$wording.'` must be a valid date (YYYY-MM-DD)'
        );
      }
      break;

    case 'select':
      $valid_ids = array_column($ucf_field['options'] ?? array(), 'id');
      if (!in_array($field['data'], $valid_ids, true))
      {
        $error = array(
          'error' => 1003,
          'message' => '`'.$wording.'` has an invalid option'
        );
      }
      break;

    default:
      break;
  }

  return $error;
}