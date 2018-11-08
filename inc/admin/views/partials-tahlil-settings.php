<?php 

namespace Tahlil\Inc\Admin\Views;

use Tahlil\Inc\Admin as Admin;

$settings = new Admin\Settings($this->plugin_text_domain);

$data = $settings->getData();

$youtube_api_key = '';

if (isset($data['youtube_api_key'])) {
	$youtube_api_key = $data['youtube_api_key'];
}

?>

<div class="wrap">
	<h1>Settings</h1>
	<form id="tahlil-admin-form" method="post">
		<table class="form-table">
			<tr scope="row">
				<th>Youtube API Key</th>
				<td>
					<input type="text" name="tahlil_youtube_api_key" value="<?php echo $youtube_api_key; ?>"/>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input class="button button-primary" type="submit" name="submit" value="save" />
		</p>
	</form>
</div>