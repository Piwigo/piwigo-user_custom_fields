<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function ucf_ws_add_methods($arr)
{
  $service = &$arr[0];

  $service->addMethod(
    'user_custom_fields.createField',
    'ucf_create_field',
    array(
      'wording' => array(
        'info' => 'Name of field'
      ),
      'order_ucf' => array(
        'type' => WS_TYPE_INT | WS_TYPE_NOTNULL,
        'info' => 'Field order'
      ),
      'active' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'Show / Hide field'
      ),
      'adminonly' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'The field is only for admin or not'
      ),
      'obligatory' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'The field is required or not'
      )
    ),
    'Create new custom fields',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => true,
    )
  );

  $service->addMethod(
    'user_custom_fields.getFields',
    'ucf_getfields',
    array(),
    'Get custom fields',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => false,
    )
  );

  $service->addMethod(
    'user_custom_fields.editField',
    'ucf_edit_field',
    array(
      'id' => array(
        'type' => WS_TYPE_NOTNULL,
        'info' => 'Field id'
      ),
      'wording' => array(
        'info' => 'Name of field',
        'flags' => WS_PARAM_OPTIONAL
      ),
      'order_ucf' => array(
        'type' => WS_TYPE_INT | WS_TYPE_NOTNULL,
        'info' => 'Field order',
        'flags' => WS_PARAM_OPTIONAL
      ),
      'active' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'Show / Hide field',
        'flags' => WS_PARAM_OPTIONAL
      ),
      'adminonly' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'The field is only for admin or not',
        'flags' => WS_PARAM_OPTIONAL
      ),
      'obligatory' => array(
        'type' => WS_TYPE_BOOL,
        'info' => 'The field is required or not',
        'flags' => WS_PARAM_OPTIONAL
      )
    ),
    'Edit new custom fields',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => true,
    )
  );

  $service->addMethod(
    'user_custom_fields.deleteField',
    'ucf_delete_field',
    array(
      'id' => array(
        'type' => WS_TYPE_NOTNULL,
        'info' => 'Field id'
      )
    ),
    'Delete custom fields',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => true,
    )
  );

  $service->addMethod(
    'user_custom_fields.sortFields',
    'ucf_sort_fields',
    array(
      'ucf_orders' => array(
        'flags' => WS_PARAM_FORCE_ARRAY,
        'info' => 'An array with `id` (for ucf id) and `order` (the position in the list)'
      )
    ),
    'Sort custom fields',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => true,
    )
  );
}

/**
 * `User Custom Fields` : createField
 */
function ucf_create_field($params, &$service)
{
  global $conf;

  $new_id = bin2hex(random_bytes(5));
  $column_name = 'ucf_' . $new_id;

  $new_conf = array(
    'id' => $new_id,
    'wording' => pwg_db_real_escape_string($params['wording']),
    'order_ucf' => $params['order_ucf'],
    'active' => $params['active'],
    'adminonly' => $params['adminonly'],
    'obligatory' => $params['obligatory'],
    'column_name' => $column_name
  );

  $conf['ucf_config']['ucf'][] = $new_conf;

  conf_update_param('ucf_config', $conf['ucf_config'], true);
  $query = '
ALTER TABLE `'.USER_INFOS_TABLE.'` 
  ADD COLUMN `' . $column_name . '` VARCHAR(255) DEFAULT NULL
;';
  pwg_query($query);

  return $new_conf;
}

/**
 * `User Custom Fields` : getFields
 */
function ucf_getfields($params, &$service)
{
  return ucf_get_fields();
}

/**
 * `User Custom Fields` : editField
 */
function ucf_edit_field($params, &$service)
{
  global $conf;
  
  $ucf = $conf['ucf_config']['ucf'];
  $current_index_ucf = array_search($params['id'], array_column($ucf, 'id'));

  if (false === $current_index_ucf)
  {
    return new PwgError(401, 'Field not found!');
  }

  $ucf[ $current_index_ucf ] = array_merge($ucf[ $current_index_ucf ], array(
    'wording' => $params['wording'] ?? pwg_db_real_escape_string($ucf[ $current_index_ucf ]['wording']),
    'order_ucf' => $params['order_ucf'] ?? $ucf[ $current_index_ucf ]['order_ucf'],
    'active' => $params['active'] ?? $ucf[ $current_index_ucf ]['active'],
    'adminonly' => $params['adminonly'] ?? $ucf[ $current_index_ucf ]['adminonly'],
    'obligatory' => $params['obligatory'] ?? $ucf[ $current_index_ucf ]['obligatory']
  ));

  $conf['ucf_config']['ucf'] = $ucf;

  conf_update_param('ucf_config', $conf['ucf_config'], true);
  return $ucf[ $current_index_ucf ];
}

/**
 * `User Custom Fields` : deleteField
 */
function ucf_delete_field($params, &$service)
{
  global $conf;
  
  $ucf = $conf['ucf_config']['ucf'];
  $current_index_ucf = array_search($params['id'], array_column($ucf, 'id'));

  if (false === $current_index_ucf)
  {
    return new PwgError(401, 'Field not found!');
  }

  $query = '
ALTER TABLE `'.USER_INFOS_TABLE.'`
  DROP COLUMN `'.$ucf[ $current_index_ucf ][ 'column_name' ].'`
;';
  pwg_query($query);

  unset($ucf[ $current_index_ucf ]);
  $conf['ucf_config']['ucf'] = array_values($ucf);

  conf_update_param('ucf_config', $conf['ucf_config'], true);
  return 'The user custom field has been deleted successfully';
}

/**
 * `User Custom Fields` : sortFields
 */
function ucf_sort_fields($params, &$service)
{
  global $conf;
  
  $ucf = $conf['ucf_config']['ucf'];
  
  foreach ($params['ucf_orders'] as $field)
  {
    if (!isset($field['id']) OR !isset($field['order']))
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Missing id or order params');
    }
    
    if (!preg_match('/^\d+$/', $field['order'])) 
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid order param must be an Integer');
    }
    
    $current_index_ucf = array_search($field['id'], array_column($ucf, 'id'));
    if (false === $current_index_ucf)
    {
      return new PwgError(401, 'Field not found!');
    }
    $ucf[ $current_index_ucf ][ 'order_ucf' ] = $field['order'];
  }

  $conf['ucf_config']['ucf'] = $ucf;
  conf_update_param('ucf_config', $conf['ucf_config'], true);
  return 'The user custom field has been sorted successfully';
}

/**
 * `User Custom Fields` : edit UserField
 */
function ucf_edit_user_field($params, &$service)
{
  global $user, $conf;

  if (!is_admin() AND $user['id'] != $params['user_id'])
  {
    return new PwgError(401, 'Access Denied');
  }

  $ucf = $conf['ucf_config']['ucf'];
  $ucf_data_new = array();
  $database_field = array();
  foreach ($params['ucf'] as $field)
  {
    if (!isset($field['ucf_id']) OR !isset($field['data']))
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Missing ucf_id or data params');
    }

    if (strlen($field['data']) > 255)
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Data field for `'.$field['ucf_id'].'` is to long (max 255 character)');
    }

    $current_index_ucf = array_search($field['ucf_id'], array_column($ucf, 'id'));
    if (false === $current_index_ucf)
    {
      return new PwgError(401, 'Field not found!');
    }

    if ($ucf[ $current_index_ucf ][ 'obligatory' ])
    {
      if (empty($field['data']) OR null == $field['data'])
      {
        return new PwgError(WS_ERR_INVALID_PARAM, '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is required');
      }
    }

    if (!$ucf[ $current_index_ucf ][ 'active' ])
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Cannot update unactive field');
    }

    if (!is_admin() and $ucf[ $current_index_ucf ][ 'adminonly' ])
    {
      return new PwgError(WS_ERR_INVALID_PARAM, '`'.$ucf[ $current_index_ucf ][ 'wording' ].'` is onlyadmin field');
    }

    $current_column_name = $ucf[ $current_index_ucf ][ 'column_name' ];

    $field['data'] = pwg_db_real_escape_string($field['data']);

    @$ucf_data_new[$params['user_id']]['user_id'] = $params['user_id'];
    $ucf_data_new[$params['user_id']][$current_column_name] = pwg_db_real_escape_string($field['data']);
    $database_field[$current_column_name] = 1;
  }

  mass_updates(
    USER_INFOS_TABLE,
    array('primary' => array('user_id'), 'update' => array_keys($database_field)),
    $ucf_data_new
  );

  return 'The user custom field has been updated successfully';
}

/**
 * `User Custom Fields` : pwg.users.setMyInfo
 */
function ucf_ws_users_setMyInfo($res, $methodName, $params)
{
  if ($methodName != 'pwg.users.setMyInfo'){
    return $res;
  }

  if (empty($params['pwg_token'])) {
    return $res;
  }

  if (isset($_POST['ucf']))
  {
    $result = ucf_save_ucf($_POST);
    if (isset($result['error']))
    {
      return new PwgError($result['error'], $result['message']);
    }
  }

  return $res;
}

/**
 * `User Custom Fields` : pwg.users.getList
 */
function ucf_ws_users_getList($users)
{
  global $conf;

  $user_ids = array();
  foreach ($users as $user_id => $user)
  {
    $user_ids[] = $user_id;
  }
  if (count($user_ids) == 0)
  {
    return $users;
  }

  $ucf_columns = array_column($conf['ucf_config']['ucf'], 'column_name');
  $query = '
    SELECT
      user_id,
      '.implode(',', $ucf_columns).'
    FROM '.USER_INFOS_TABLE.'
      WHERE user_id IN ('.implode(',', $user_ids).')
  ;';
  $result = pwg_query($query);
  while ($row = pwg_db_fetch_assoc($result)){
    foreach ($ucf_columns as $col)
    {
      $users[$row['user_id']][$col] = $row[$col];
    }
  }
  return $users;
}
