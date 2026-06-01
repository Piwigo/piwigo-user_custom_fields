{combine_script id='ucf_block' load='footer' path="{$UCF_PATH}/js/ucf_fields.js"}
{footer_script}
const USE_STANDARD_PAGE = {($USE_STANDARD_PAGE) ? "true" : "false"}
{/footer_script}
<div id="ucf_fields" class="form plugins fields" style="display: none;">
  {foreach from=$UCF_FIELDS item=ucf key=k_ucf}
    {* TYPE => TEXT *}
    {if $ucf.type === 'text'}
      <div class="ucf_container">
        <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
        <div class="ucf_input_container input-container">
          <input type="hidden" class="ucf-id" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" data-type="{$ucf.type}" />
          <input name="ucf[{$k_ucf}][data]" id="ucf_{$ucf.id}" type="text" value="{$ucf.data|escape:html}"
            {if $ucf.obligatory}required{/if} />
        </div>
      </div>  
    {/if}

    {* TYPE => TEXTAREA *}
    {if $ucf.type === 'textarea'}
      <div class="ucf_container">
        <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
        <div class="ucf_input_container input-container">
          <input type="hidden" class="ucf-id" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" data-type="{$ucf.type}" />
          <textarea 
            resize="false"
            rows="8"
            class="form-control"
            name="ucf[{$k_ucf}][data]"
            id="ucf_{$ucf.id}"
            {if $ucf.obligatory}required{/if}
          >{$ucf.data|escape:html}</textarea>
        </div>
      </div>
    {/if}

    {* TYPE => CHECKBOX *}
    {if $ucf.type === 'checkbox'}
      <div class="ucf_container input-container">
        <input type="hidden" class="ucf-id" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" data-type="{$ucf.type}" />
        <input type="hidden" name="ucf[{$k_ucf}][data]" value="false" />
        <input
          class="form-control"
          style="width: unset;"
          type="checkbox"
          name="ucf[{$k_ucf}][data]"
          id="ucf_{$ucf.id}"
          value="true"
          {if $ucf.data}checked{/if}
          {if $ucf.obligatory}required{/if}
        >
        <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
      </div>
    {/if}

    {* TYPE => DATE *}
    {if $ucf.type === 'date'}
      <div class="ucf_container">
        <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
        <div class="ucf_input_container input-container">
          <input type="hidden" class="ucf-id" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" data-type="{$ucf.type}" />
          <input 
            type="date"
            name="ucf[{$k_ucf}][data]"
            id="ucf_{$ucf.id}"
            value="{$ucf.data}"
            {if $ucf.obligatory}required{/if}
          />
        </div>
      </div>
    {/if}

    
    {* TYPE => SELECT *}
    {if $ucf.type === 'select'}
      <div class="ucf_container">
        <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
        <div class="ucf_input_container input-container">
          <input type="hidden" class="ucf-id" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" data-type="{$ucf.type}" />
          <select
            class="form-control"
            name="ucf[{$k_ucf}][data]"
            id="ucf_{$ucf.id}"
          >
              <option value="">----</option>
            {foreach from=$ucf.options item=option}
              <option value="{$option.id}" {if $option.id === $ucf.data}selected{/if}>{$option.label|escape:html}</option>
            {/foreach}
          </select>
        </div>
      </div>
    {/if}
  {/foreach}
  <label class="required-fields">* {"Required fields"|translate|escape:html}</label>
</div>
{html_style}
.required-fields {
  margin: 0 !important;
  font-size: 0.8em !important;
}
{/html_style}