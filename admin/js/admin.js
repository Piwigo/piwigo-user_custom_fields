// +-----------------------------------------------------------------------+
// | Definition of constants                                               |
// +-----------------------------------------------------------------------+

let ucf_data = [];
let in_edit = false;
let is_clicked = false;
const ucf_modal = $('#ucf_modal');
const ucf_loading = $('#ucf_loading');
const ucf_no_fields = $('#ufc_no_fields');
const ucf_template = $('#ucf_template_line');
const ucf_line_content = $('#tab_body_content');
const ucf_wording = $('#ucf_wording');
const ucf_active = $('#ucf_hide');
const ucf_adminonly = $('#ucf_adminonly');
const ucf_obligatory = $('#ucf_obligatory');

const ucf_modal_error = $('#ucf_modal_error');
const ucf_modal_title = $('#ufc_modal_title');
const ucf_modal_icon = $('#ucf_modal_icon');
const ucf_modal_save = $('#ucf_modal_save');

const ucf_delete_modal = $('#ucf_delete');
const ucf_delete_name = $('#ucf_delete_name');
const ucf_delete_close = $('#ucf_delete_close');
const ucf_delete_btn = $('#ucf_delete_btn');

// +-----------------------------------------------------------------------+
// | On dom ready                                                          |
// +-----------------------------------------------------------------------+

$(function () {
  $('#ucf_create_field').on('click', function () {
    ucfOpenModal();
  });

  $('.ucf_modal_close').on('click', function () {
    ucfCloseModal();
  });

  ucf_delete_close.on('click', function() {
    ucfCloseDeleteModal();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      if (ucf_modal.is(':visible')) ucfCloseModal();
      if (ucf_delete_modal.is(':visible')) ucfCloseDeleteModal();
    }

    if (e.key === 'Enter' && ucf_modal.is(':visible')) {
      ucf_modal_save.trigger('click');
    }
  });

  ucf_wording.on('keydown', function() {
    ucfModalHideError();
  });

  ucf_modal_save.on('click', function() {
    if (is_clicked) return;
    is_clicked = true;
    const values = {
      wording: ucf_wording.val(),
      order_ucf: 1,
      active: !ucf_active.is(':checked'),
      adminonly: ucf_adminonly.is(':checked'),
      obligatory: ucf_obligatory.is(':checked')
    }

    if (values.wording == '') {
      ucfModalShowError(str_all_fields);
      return;
    }

    if (!in_edit) {
      ucfCreateField(values);
    } else {
      ucfEditField({...values, id: in_edit});
    }
  });

  ucfGetFields();

  $("#tab_body_content").sortable({
    update: function( event, ui ) {
      let order = 1;
      const new_order = [];
      $('#tab_body_content > div').each((i, element) => {
        new_order.push({ order, id: $(element).data('id')});
        order++
      });
      
      // for debug
      // console.log(new_order);
      ucfSortFields(new_order);
    }
  });
});

// +-----------------------------------------------------------------------+
// | Definition of functions                                               |
// +-----------------------------------------------------------------------+

function ucfCloseModal() {
  ucf_modal.fadeOut(() => {
    ucfResetModal();
    is_clicked = false;
  });
}

function ucfOpenModal() {
  ucf_modal.fadeIn();
  ucf_wording.trigger('focus');
}

function ucfResetModal() {
  ucf_wording.val('');
  ucf_active.prop('checked', false);
  ucf_adminonly.prop('checked', false);
  ucf_obligatory.prop('checked', false);

  in_edit = false;
  ucf_modal_title.html(str_modal_title_new);
  ucf_modal_icon.removeClass('icon-pencil').addClass('icon-plus-circled');

  ucfModalHideError();
}

function ucfFillAndOpenModal(ucf) {
  ucf_wording.val(ucf.wording);
  ucf_active.prop('checked', !Number(ucf.active) ? true : false);
  ucf_adminonly.prop('checked', Number(ucf.adminonly) ? true : false);
  ucf_obligatory.prop('checked', Number(ucf.obligatory) ? true : false);

  ucf_modal_title.html(str_modal_title_edit);
  ucf_modal_icon.removeClass('icon-plus-circled').addClass('icon-pencil');

  in_edit = ucf.id;
  ucfOpenModal();
}

function ucfModalShowError(message) {
  ucf_modal_error.html(message);
  ucf_modal_error.removeClass('ucf-hidden');
  is_clicked = false;
}

function ucfModalHideError() {
  ucf_modal_error.addClass('ucf-hidden');
}

function ucfDisplayFields(data) {
  ucf_loading.hide();
  ucf_no_fields.hide();

  if (!data || !data.length) {
    ucf_no_fields.show();
    return;
  }

  ucf_data = [...ucf_data, ...data];
  // display line
  data.forEach((ucf, i) => {
    const index = ucf_data.findIndex((u) => u.id == ucf.id);
    if (index !== -1) {
      ucf_data[index] = ucf;
    } else {
      ucf_data.push(ucf);
    }

    const template = ucf_template.clone();
    template.attr('data-id', ucf.id);
    template.attr('id', `ucf_${ucf.id}`);
    template.find('.ucf-tab-edit').attr('data-id', ucf.id);
    template.find('.ucf-tab-delete').attr('data-id', ucf.id);

    template.find('.ucf-tab-wording p').text(ucf.wording);
    template.find('.ucf-tab-adminonly p').html(Number(ucf.adminonly) ? str_yes : str_no);
    template.find('.ucf-tab-hide p').html(!Number(ucf.active) ? str_yes : str_no);
    template.find('.ucf-tab-obligatory p').html(Number(ucf.obligatory) ? str_yes : str_no);

    ucf_line_content.append(template);
  });

  // line event
  $('.ucf-tab-edit').off('click').on('click', function() {
    const ucf_id = $(this).data('id');
    const currentUcf = ucf_data.filter((ucf) => ucf.id == ucf_id);
    if (!currentUcf.length) {
      return;
    }
    ucfFillAndOpenModal(currentUcf[0]);
  });

  $('.ucf-tab-delete').off('click').on('click', function() {
    const ucf_id = $(this).data('id');
    const currentUcf = ucf_data.filter((ucf) => ucf.id == ucf_id);
    if (!currentUcf.length) {
      return;
    }
    ucfFillAndOpenDeleteModal(currentUcf[0])
  });
}

function ucfEditDisplayedField(ucf) {
  const i = ucf_data.findIndex((u) => u.id == ucf.id);
  if (i !== -1) ucf_data[i] = ucf;
  const line = $(`#ucf_${ucf.id}`);
  line.find('.ucf-tab-wording p').text(ucf.wording);
  line.find('.ucf-tab-adminonly p').html(Number(ucf.adminonly) ? str_yes : str_no);
  line.find('.ucf-tab-hide p').html(!Number(ucf.active) ? str_yes : str_no);
  line.find('.ucf-tab-obligatory p').html(Number(ucf.obligatory) ? str_yes : str_no);
}

function ucfFillAndOpenDeleteModal(ucf) {
  ucf_delete_name.html(sprintf(str_delete_field, ucf.wording));
  ucf_delete_modal.fadeIn();

  ucf_delete_btn.one('click', function() {
    ucfDeleteField(ucf.id);
  });
}

function ucfCloseDeleteModal() {
  ucf_delete_btn.off('click');
  ucf_delete_modal.fadeOut();
}

// +-----------------------------------------------------------------------+
// | Definition of ajax functions                                          |
// +-----------------------------------------------------------------------+

function ucfGetFields() {
  $.ajax({
    url: 'ws.php?format=json&method=user_custom_fields.getFields',
    dataType: 'JSON',
    success: function (res) {
      if (res.stat === 'ok') {
        ucf_line_content.empty();
        ucfDisplayFields(res.result)
      } else {
        // error
      }
    },
    error: function (e) {
      console.log(e);
    }
  });
}

function ucfCreateField(data) {
  $.ajax({
    url: 'ws.php?format=json&method=user_custom_fields.createField',
    type: 'POST',
    dataType: 'JSON',
    data,
    success: function (res) {
      if (res.stat === 'ok') {
        ucfDisplayFields([res.result]);
        ucfCloseModal();
      } else {
        ucfModalShowError(res.message);
      }
    },
    error: function(e) {
      console.log(e);
      ucfModalShowError(e.responseJSON?.message ?? 'Internal Server Error.. Try later..');
    }
  });
}

function ucfEditField(data) {
  $.ajax({
    url: 'ws.php?format=json&method=user_custom_fields.editField',
    type: 'POST',
    dataType: 'JSON',
    data,
    success: function (res) {
      if (res.stat === 'ok') {
        ucfEditDisplayedField(res.result);
        ucfCloseModal();
      } else {
        ucfModalShowError(res.message);
      }
    },
    error: function(e) {
      console.log(e);
      ucfModalShowError(e.responseJSON?.message ?? 'Internal Server Error.. Try later..');
    }
  });
}

function ucfDeleteField(id) {
  $.ajax({
    url: 'ws.php?format=json&method=user_custom_fields.deleteField',
    type: 'POST',
    dataType: 'JSON',
    data: {
      id
    },
    success: function(res) {
      if (res.stat === 'ok') {
        $(`#ucf_${id}`).remove();
        ucfCloseDeleteModal();
      } else {
        // error
        console.log(res);
      }
    },
    error: function(e) {
      console.log(e);
    }
  });
}

function ucfSortFields(data) {
  $.ajax({
    url: 'ws.php?format=json&method=user_custom_fields.sortFields',
    type: 'POST',
    dataType: 'json',
    data: {
      ucf_orders: data
    },
    success: function(res) {
      console.log(res);
    },
    error: function(e) {
      console.log(e);
    }
  });
}
