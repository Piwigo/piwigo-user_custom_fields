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
    UCF_FIELDS.find('input').each((i, element) => {
      const el = $(element);
      const inputName = el.attr('name');
      const inputValue = el.val();
      values[inputName] = inputValue;
    });

    setInfos({ ...values, email });
  });
}