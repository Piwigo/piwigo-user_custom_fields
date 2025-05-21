{combine_script id='ucf_block' load='footer' path="{$UCF_PATH}/js/ucf_fields.js"}
{footer_script}
const USE_STANDARD_PAGE = {($USE_STANDARD_PAGE) ? "true" : "false"}
{/footer_script}
<div id="ucf_fields" class="form plugins fields" style="display: none;">
  {foreach from=$UCF_FIELDS item=$ucf key=$k_ucf}
    <div class="ucf_container">
      <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
      <div class="ucf_input_container input-container">
        <input type="hidden" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" />
        <input name="ucf[{$k_ucf}][data]" id="ucf_{$ucf.id}" type="text" value="{$ucf.data}"
          {if $ucf.obligatory}required{/if} />
      </div>
    </div>
  {/foreach}
  <label class="required-fields">* {"Required fields"|translate|escape:html}</label>
</div>
{html_style}
.required-fields {
  margin: 0 !important;
  font-size: 0.8em !important;
}
{/html_style}