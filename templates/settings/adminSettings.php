<?php
script('firebasepushnotifications', 'adminSettings');
style('firebasepushnotifications', 'outofstylesheet');
/**
 * Created by PhpStorm.
 * User: paolo
 * Date: 08/05/2017
 * Time: 15:27
 */
use OCA\Firebasepushnotifications\Entities\DummyPushType;
use OCA\Firebasepushnotifications\Entities\FirebaseAppKey;
use OCA\Firebasepushnotifications\Entities\PushTypesConfiguration;

/** @var $l \OCP\IL10N */
/** @var $_ array */

echo '<div>';
$parameters = [];
//echo $_['pushConf'];
/** @var $conf PushTypesConfiguration */
$conf = $_['pushConf'];
/** @var $dpt DummyPushType */
?>

	<h2 class="icon-firebase">Firebase Push Notifications Configuration -
		Admin</h2>

	<?php
	if(isset($_['firebase'])){
		/** @var FirebaseAppKey $appConf */
		$appConf = $_['firebase'];
		print_unescaped('<h3>Firebase Server Key:</h3>');
		print_unescaped('<form id="firebase_key_form" class="section">');
		print_unescaped('<p><label for="appName">Firebase Server App: </label><em id="appName">' . $appConf->getAppName() . '</em></p>' .
			'<p><label for="serverFirebaseKey">Firebase Server Key: </label><input type="text" name="serverKey" id="serverFirebaseKey" style="min-width:450px;" disabled value="' . $appConf->getServerKey() . '">' .
						'<span id="firebase_key_settings_activity" class="msg"></span><img id="lockToggle" src="'.image_path('firebasepushnotifications','lock.png').'" alt="Qui Va un immagine"/></p>');
		print_unescaped('</form>');
	}
	?>
	<h3>Push Settings:</h3>
<div>
	<form id="firebasepushnotifications_settings" class="section" method="POST">
		<table class="settingsTable grid">
			<thead>
			<tr>
				<th class="pushSettingHeader">Push Type</th>
				<th class="pushSettingHeader">Description</th>
				<th class="pushSettingHeader">isEnabled</th>
			</tr>
			</thead>
			<tbody>

			<?php
			print_unescaped('<tr><td class="pushSetting">Send Enabled</td><td>Whether to send any notification or not</td><td><input type="checkbox" id="SendEnabled" name="SendEnabled" value="SendEnabled" ' . ($conf->getSendEnabled() ? 'checked' : '') . ' ></td></tr>');
			print_unescaped('<tr><td class="pushSetting">Send To Same User</td><td>Send notification to acting user?</td><td><input type="checkbox" id="SendToSameUser" name="SendToSameUser" value="SendToSameUser" ' . ($conf->getSendToSameUser() ? 'checked' : '') . ' ></td></tr>');

			$i = 0;
			foreach ($conf->getPushTypes() as $key => $dpt) {
				print_unescaped('<tr><td class="pushSetting">' . $dpt->pushType .
					'</td><td >' . $dpt->pushDescription . '</td>' .
					'<td class="pushSetting"><input type="checkbox" id="PushType:'
					. $i . '" name="PushType:' . $i . '" value="' . $dpt->pushType
					. '" ' . ($dpt->isEnabled ? 'checked' : '') .
					' ><span id="settings_activity" class="msg"></span></td></tr>');
				$i++;
			}
			?>
			</tbody>
		</table>
	</form>
</div>



