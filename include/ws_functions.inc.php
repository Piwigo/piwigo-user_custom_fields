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
      'type' => array(
        'info' => 'Type of field',
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
      ),
      'select_options' => array(
        'info' => 'Options when type is select',
        'flags' => WS_PARAM_FORCE_ARRAY | WS_PARAM_OPTIONAL,
      ),
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
      ),
      'select_options' => array(
        'info' => 'Options when type is select',
        'flags' => WS_PARAM_FORCE_ARRAY | WS_PARAM_OPTIONAL,
      ),
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

  if (!in_array($params['type'], $conf['ucf_config']['allowed_type']))
  {
    return new PwgError(422, 'Type must be: ' . implode(' ,', $conf['ucf_config']['allowed_type']));
  }

  $new_id = bin2hex(random_bytes(5));
  $column_name = 'ucf_' . $new_id;

  $new_conf = array(
    'id' => $new_id,
    'wording' => stripslashes($params['wording']),
    'type' => $params['type'],
    'order_ucf' => $params['order_ucf'],
    'active' => $params['active'],
    'adminonly' => $params['adminonly'],
    'obligatory' => $params['obligatory'],
    'column_name' => $column_name
  );

  if ('select' === $params['type'])
  {
    if (empty($params['select_options']))
    {
      return new PwgError(422, 'Please add an option');
    }

    foreach($params['select_options'] as $option)
    {
      if (empty($option['label']) || '' === trim($option['label']))
      {
        continue;
      }

      $new_conf['options'][] = array(
        'id' => bin2hex(random_bytes(5)),
        'label' => stripslashes($option['label']),
      );
    }
  }

  $conf['ucf_config']['ucf'][] = $new_conf;
  $column_type = ucf_get_column_type($params['type']);
  $query = '
ALTER TABLE `'.USER_INFOS_TABLE.'` 
  ADD COLUMN `' . $column_name . '` '.$column_type.'
;';

  // save
  pwg_query($query);
  conf_update_param('ucf_config', $conf['ucf_config'], true);

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
    return new PwgError(404, 'Field not found!');
  }

  $ucf[ $current_index_ucf ] = array_merge($ucf[ $current_index_ucf ], array(
    'wording' => isset($params['wording'])
      ? stripslashes($params['wording'])
      : $ucf[ $current_index_ucf ]['wording'],
    'order_ucf' => $params['order_ucf'] ?? $ucf[ $current_index_ucf ]['order_ucf'],
    'active' => $params['active'] ?? $ucf[ $current_index_ucf ]['active'],
    'adminonly' => $params['adminonly'] ?? $ucf[ $current_index_ucf ]['adminonly'],
    'obligatory' => $params['obligatory'] ?? $ucf[ $current_index_ucf ]['obligatory']
  ));

  if ('select' === $ucf[ $current_index_ucf ]['type'])
  {
    if (empty($params['select_options']))
    {
      return new PwgError(422, 'Please add an option');
    }

    $existing_by_id = array_column($ucf[ $current_index_ucf ]['options'] ?? array(), null, 'id');
    $new_options = array();

    foreach ($params['select_options'] as $option)
    {
      if (empty($option['label']) || '' === trim($option['label']))
      {
        continue;
      }

      if (!empty($option['id']))
      {
        if (!isset($existing_by_id[$option['id']]))
        {
          return new PwgError(422, 'Unknown option id: '.$option['id']);
        }
        $option_id = $option['id'];
      }
      else
      {
        $option_id = bin2hex(random_bytes(5));
      }

      $new_options[] = array(
        'id' => $option_id,
        'label' => stripslashes($option['label']),
      );
    }

    if (empty($new_options))
    {
      return new PwgError(422, 'Please add an option');
    }

    $ucf[ $current_index_ucf ]['options'] = $new_options;
  }

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
  if (empty($ucf_columns))
  {
    return $users;
  }

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
