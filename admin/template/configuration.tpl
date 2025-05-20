{combine_css path="{$UCF_PATH}/admin/css/admin.css" order=0}
{combine_css path="admin/themes/default/fontello/css/animation.css" order=10}
{combine_script id='ucf_admin' load='footer' path="{$UCF_PATH}admin/js/admin.js"}
{combine_script id='common' load='footer' path='admin/themes/default/js/common.js'}
{combine_script id='jquery.ui.sortable' require='jquery.ui' load='header' path='themes/default/js/ui/minified/jquery.ui.sortable.min.js'}
{if $themeconf['colorscheme'] == 'dark'}
  {combine_css path="{$UCF_PATH}admin/css/plugin_dark.css" order=3}
{/if}
{footer_script}
const str_yes = "{'Yes'|translate|escape:javascript}";
const str_no = "{'No'|translate|escape:javascript}";
const str_all_fields = "{'Please complete all fields'|translate|escape:javascript}";
const str_modal_title_new = "{'Create new custom fields'|translate|escape:javascript}";
const str_modal_title_edit = "{'Edit a custom field'|translate|escape:javascript}";
const str_delete_field = "{'Are you sure you want to delete the "%s" field?'|translate|escape:javascript}";
{/footer_script}
<div class="titrePage">
  <h2>{'Manage user custom fields'|@translate}</h2>
</div>
<div class="ucf-container">
  <p class="head-button-2 icon-plus-circled" id="ucf_create_field">{'Create new custom fields'|@translate}</p>
  <div>
    <div class="tab-header">
      <div class="tab-header-wording">
        <p>{'Wording'|translate}</p>
      </div>
      <div class="tab-header-adminonly">
        <p>{'Admin only'|translate}</p>
      </div>
      <div class="tab-header-hide">
        <p>{'Hide'|translate}</p>
      </div>
      <div class="tab-header-obligatory">
        <p>{'Obligatory'|translate}</p>
      </div>
      <div class="tab-header-action">
      </div>
    </div>

    <div class="loading" id="ucf_loading">
      <span class="icon-spin6 animate-spin"></span>
    </div>

    <div class="nofields" id="ufc_no_fields">
      <p>{'To get started, add a new custom field.'|@translate}</p>
    </div>

    <div class="tab-body-content" id="tab_body_content">
      <div class="tab-body ucf-tab-line line" id="ucf_template_line" data-id="-1">
        <div class="ucf-tab-wording">
          <i class="icon-grip-vertical-solid"></i>
          <p></p>
        </div>
        <div class="ucf-tab-adminonly">
          <p></p>
        </div>
        <div class="ucf-tab-hide">
          <p></p>
        </div>
        <div class="ucf-tab-obligatory">
          <p></p>
        </div>
        <div class="ucf-tab-action">
          <p class="icon-pencil ucf-tab-edit" data-id="-1"></p>
          <p class="icon-trash-1 ucf-tab-delete" data-id="-1"></p>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="bg-modal" id="ucf_modal" data-edit="-1">
  <div class="ucf-modal-content">
    <a class="close-modal icon-cancel ucf_modal_close"></a>

    <div class="ucf-icon-header">
      <span class="ufc-icon icon-blue icon-plus-circled" id="ucf_modal_icon"></span>
    </div>
    <p class="ucf-modal-title" id="ufc_modal_title">{'Create new custom fields'|translate}</p>

    <div class="ucf-modal-body">
      <div class="ucf-modal-field">
        <label class="ucf-modal-field-label" for="ucf_wording">{'Wording'|translate}</label>
        <input class="ucf-modal-input" type="text" name="ucf_wording" id="ucf_wording" />
      </div>
      <div>
        <label class="switch">
          <input type="checkbox" name="ucf_adminonly" id="ucf_adminonly">
          <span class="slider round"></span>
        </label>
        <label for="ucf_adminonly">{'Admin only'|translate}</label>
      </div>
      <div>
        <label class="switch">
          <input type="checkbox" name="ucf_hide" id="ucf_hide">
          <span class="slider round"></span>
        </label>
        <label for="ucf_hide">{'Hide'|translate}</label>
      </div>
      <div>
        <label class="switch">
          <input type="checkbox" name="ucf_obligatory" id="ucf_obligatory">
          <span class="slider round"></span>
        </label>
        <label for="ucf_obligatory">{'Obligatory'|translate}</label>
      </div>
    </div>
    <div class="ucf-modal-footer">
      <p class="ucf-modal-close ucf_modal_close">{'Close'|translate}</p>
      <div class="ucf-modal-footer-group">
        <p class="ucf-hidden ucf-modal-error icon-red-error" id="ucf_modal_error"></p>
        <p class="ucf-modal-save" id="ucf_modal_save"><span class="icon-floppy"></span>{'Save'|translate}</p>
      </div>
    </div>
  </div>
</div>

<div class="bg-modal" id="ucf_delete">
  <div class="ucf-modal-content">
    <p class="ucf-modal-title" id="ucf_delete_name"></p>
    <div class="ucf-modal-footer">
      <p class="ucf-delete-close" id="ucf_delete_close">{'No, I have changed my mind'|translate}</p>
      <p class="ucf-delete-btn" id="ucf_delete_btn">{'Yes, I am sure'|translate}</p>
    </div>
  </div>
</div>