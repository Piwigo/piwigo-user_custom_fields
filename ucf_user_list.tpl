{combine_script id="jquery.ucf" load='footer' path="{$UCF_PATH}/js/ucf.js"}
{footer_script}
const str_ucf_name = "{'User custom fields'|@translate|escape:javascript}"
{/footer_script}
<div id="ucfArea">
 <p>For the moment, we're keeping the option of opening the plugin's config page to modify custom fields.</p>
</div>
{html_style}
  #ucfArea {
    display: none;
    width: 100%;
    height: 100%;
  }
{/html_style}