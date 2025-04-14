$(function() {
	plugin_add_tab_in_user_modal(
		"User custom fields",
		'ucfArea',
		null
	);
	
	const ucfUrl = "admin.php?page=plugin-user_custom_fields";
	const ucfTabName = $('#name_tab_usercustomfields');
	// change l10n plugin name after because for example in french we have some apostrophe
	ucfTabName.html(str_ucf_name).attr('title', str_ucf_name).tipTip();

	// For the moment, we're keeping the option of opening the plugin's config page to modify custom fields.
	ucfTabName.on('mouseup', function () {
		const user = current_users[last_user_index];
		window.location.href = ucfUrl + `-edit_user&ucfiduser=${user.id}&ucfusername=${user.username}`;
		return;
	});
})