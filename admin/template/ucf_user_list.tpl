{combine_script id="ucf_user_list" load="footer" path="{$UCF_PATH}admin/js/ucf_user_list.js"}
{footer_script}
const UCF_NAME = "{$UCF_NAME}";
{/footer_script}
<div id="ucf_area">
  {foreach from=$UCF_FIELDS item=$ucf key=$k_ucf}
    <div class="ucf-userdata" id="{$ucf.id}" data-required="{($ucf.obligatory) ? true : false}">
      <p class="user-property-label">{$ucf.wording} {if $ucf.obligatory}*{/if} {if $ucf.adminonly}({"Admin only"|translate|escape:html}){/if}</p>
      <input type="text" class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}">
    </div>
  {/foreach}
</div>
{html_style}
#ucf_area {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  height: 100%;
}
#ucf_area p {
  margin: 0;
}
{/html_style}