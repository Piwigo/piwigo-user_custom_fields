{combine_script id="ucf_user_list" load="footer" path="{$UCF_PATH}admin/js/ucf_user_list.js"}
<div id="ucf_area">
  {foreach from=$UCF_FIELDS item=ucf key=k_ucf}
    <div class="ucf-userdata" id="{$ucf.id}" data-required="{($ucf.obligatory) ? true : false}" data-type="{$ucf.type}">
      <p class="user-property-label">{$ucf.wording|escape:html} {if $ucf.obligatory}*{/if} {if $ucf.adminonly}({"Admin only"|translate|escape:html}){/if}</p>

      {* TYPE => TEXT *}
      {if $ucf.type === 'text'}
        <input type="text" class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}">
      {/if}

      {* TYPE => TEXTAREA *}
      {if $ucf.type === 'textarea'}
        <textarea class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}" rows="8"></textarea>
      {/if}

      {* TYPE => CHECKBOX *}
      {if $ucf.type === 'checkbox'}
        <input type="checkbox" class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}" value="true" style="width: unset;" />
      {/if}

      {* TYPE => DATE *}
      {if $ucf.type === 'date'}
        <input type="date" class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}" />
      {/if}

      {* TYPE => SELECT *}
      {if $ucf.type === 'select'}
        <select class="user-property-input" id="{$ucf.column_name}" name="field-{$ucf.id}">
          <option value="">----</option>
          {foreach from=$ucf.options item=option}
            <option value="{$option.id}">{$option.label|escape:html}</option>
          {/foreach}
        </select>
      {/if}
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
  overflow-y: auto;
}
#ucf_area p {
  margin: 0;
}
{/html_style}