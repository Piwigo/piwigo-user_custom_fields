const UCF_FIELDS = $('#ucf_fields');
const IN_PROFILE = $('#theProfilePage').length > 0;
$(function () {
  if (USE_STANDARD_PAGE) {
    UCF_FIELDS.removeClass('form plugins fields');
    if (IN_PROFILE) {
      $('#account-display .save').before(UCF_FIELDS);
      ucfProfileEvent();
    } else {
      $('#register-form .column-flex:last').before(UCF_FIELDS);
    }

  } else {
    if (IN_PROFILE) {
      $('#profile fieldset:first').append(UCF_FIELDS);
    } else {
      $('form[name="register_form"] fieldset:first').append(UCF_FIELDS);
    }
  }

  UCF_FIELDS.removeAttr('style');
});

function ucfProfileEvent() {
  $('#save_account').on('click', function (e) {
    e.stopImmediatePropagation(); // to prevent the original click from profile.js
    const values = {};
    const email = $('#email').val();
    UCF_FIELDS.find('input.ucf-id').each((i, element) => {
      const el = $(element);
      const ucf_id = el.val();
      const ucf_id_name = el.attr('name');
      const ucf_value_name = $(`#ucf_${ucf_id}`).attr('name');
      const ucf_type = el.data('type');

      let value;
      switch (ucf_type) {
        case 'checkbox':
          value = $(`#ucf_${ucf_id}`).is(':checked') ? 'true' : 'false';
          break;

        default:
          value = $(`#ucf_${ucf_id}`).val();
          break;
      }

      values[ucf_id_name] = ucf_id;
      values[ucf_value_name] = value;
    });

    setInfos({ ...values, email });
  });
}