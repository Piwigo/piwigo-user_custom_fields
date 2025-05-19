{foreach from=$UCF_FIELDS item=$ucf key=$k_ucf}
  <div>
    <label for="ucf_{$ucf.id}">{$ucf.wording} {if $ucf.obligatory}*{/if}</label>
    <div class="input-container">
      <input type="hidden" name="ucf[{$k_ucf}][ucf_id]" value="{$ucf.id}" />
      <input name="ucf[{$k_ucf}][data]" id="ucf_{$ucf.id}" type="text" value="{$ucf.data}"
        {if $ucf.obligatory}required{/if} />
    </div>
  </div>
{/foreach}
<p class="required-fields">* {"Required fields"|translate|escape:html}</p>
{html_style}
.required-fields {
  margin: 0 !important;
}
{/html_style}