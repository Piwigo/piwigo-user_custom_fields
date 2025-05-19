$(function() {
  plugin_add_tab_in_user_modal(
    UCF_NAME,
    'ucf_area',
    null,
    () => {
      // Function to retrieve data from the HTML element and save it via your API method
      const currentUser = current_users.filter((u) => u.id == last_user_id);
      if (!currentUser.length) {
        console.log('Error set data ucf_area');
        return;
      };

      const fields = [];
      for (const [key, value] of Object.entries(currentUser[0])) {
        if (key.startsWith('ucf') && $(`#${key}`).length) {
          fields.push({
            ucf_id: key.split('_')[1],
            data: $(`#${key}`).val()
          });
        }
      }
      
      $.ajax({
        url: 'ws.php?format=json&method=pwg.users.setMyInfo',
        type: 'POST',
        dataType: 'json',
        data: {
          user_id: last_user_id,
          ucf: fields,
          pwg_token
        },
        success: function(res) {
          if (res.stat == 'ok') {
            // all good
          } else {
            $("#UserList .update-user-fail").html(res.message ?? errorStr);
            $("#UserList .update-user-fail").fadeIn();
          }
        },
        error: function(e) {
          console.log(e);
          $("#UserList .update-user-fail").html(e.responseJSON?.message ?? errorStr);
          $("#UserList .update-user-fail").fadeIn();
        }
      });
    },
    () => {
      // Function to retrieve data from the database via your API method and display it in the user modal tab
      $('.ucf-userdata input').val('');
      const currentUser = current_users.filter((u) => u.id == last_user_id);
      if (!currentUser.length) {
        console.log('Error get data ucf_area');
        return;
      };
      
      for (const [key, value] of Object.entries(currentUser[0])) {
        if (key.startsWith('ucf') && $(`#${key}`).length) {
          $(`#${key}`).val(value);
        }
      }
    }
  );
});