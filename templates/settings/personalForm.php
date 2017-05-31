<?php
/**
 * Created by PhpStorm.
 * User: paolo
 * Date: 10/05/2017
 * Time: 17:39
 */
use OCA\Firebasepushnotifications\DB\FirebaseConfHandler;
use OCA\Firebasepushnotifications\Entities\FirebaseToken;
use OCA\Firebasepushnotifications\Entities\PushTypesConfiguration;

?>
<?php
try {
	print_unescaped('<div class="section">');
	print_unescaped('<h2>Firebase User Settings</h2><span id="firebase_user_settings_activity" class="msg"></span>');

	if (isset($_['userSettings']) && isset($_['userSettings'][FirebaseConfHandler::PUSH_CONFIG_KEY])) {
		$conf = $_['userSettings'][FirebaseConfHandler::PUSH_CONFIG_KEY];
		\OC::$server->getLogger()->info('Personal Settings Form: ' . print_r($conf, true) . ' ');
		$conf = PushTypesConfiguration::fromJSON($conf);
		if (isset($conf)) {
			print_unescaped('<form id="firebaseUserSettings" ><table class="grid" id="userSettings">' .
				'<thead>' .
				'<tr>' .
				'<th class="">Push Type</th>' .
				'<th class="">Description</th>' .
				'<th class="">isEnabled</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>');
			print_unescaped('<tr><td class="">Send Enabled</td><td>Whether to send any notification or not</td><td><input type="checkbox" id="SendEnabled" name="SendEnabled" value="SendEnabled" ' . ($conf->getSendEnabled() ? 'checked' : '') . ' ></td></tr>');
			print_unescaped('<tr><td class="">Send To Same User</td><td>Send notification to acting user?</td><td><input type="checkbox" id="SendToSameUser" name="SendToSameUser" value="SendToSameUser" ' . ($conf->getSendToSameUser() ? 'checked' : '') . ' ></td></tr>');

			$i = 0;
			foreach ($conf->getPushTypes() as $key => $dpt) {
				print_unescaped('<tr><td class="">' . $dpt->pushType .
					'</td><td >' . $dpt->pushDescription . '</td>' .
					'<td class=""><input type="checkbox" id="PushType:'
					. $i . '" name="PushType:' . $i . '" value="' . $dpt->pushType
					. '" ' . ($dpt->isEnabled ? 'checked' : '') .
					' ></td></tr>');
				$i++;
			}

			print_unescaped('</tbody></table></form>');
		}
	}

	if (isset($_['tokens'])) {

		print_unescaped('<h2>Firebase User Tokens</h2><span id="opProgress"></span>');
		print_unescaped('<form id="tokensForm">');
		print_unescaped('<table id="tokenTable" class="grid" >');
		print_unescaped('<thead><tr><th>Id</th><th>DeviceType</th><th>LastUsed</th><th>Locale</th><th>Risorsa</th> <th>Token (Parziale)</th>' . '</tr></thead>');
		/** @var FirebaseToken $tokenEntry */
		foreach ($_['tokens'] as $key => $tokenEntry) {
			print_unescaped(
				'<tr class="tokenRow" id="row'.$tokenEntry->getId().'">'.
					'<td>' . $tokenEntry->getId() . '</td>' .
					'<td>' . $tokenEntry->getDeviceTypeAsString() .
					'</td><td>' . $tokenEntry->getReadableLastUsed() .
					'</td><td>' . $tokenEntry->getLocale() . '</td>' .
					'<td>' . $tokenEntry->getResource() . '</td>' .
					'<td>' . $tokenEntry->getClippedToken() . '</td>' .
					'<td><img id="' . $tokenEntry->getId() . '" class="tokenDelete" src="' .image_path('core', 'actions/delete.svg')  . '" alt="delete"/></td>'.
				'</tr>'); //USe $('.className').stuff() tomorrow
		}
		print_unescaped('</table >');
		if (count($_['tokens']) > 0) {
			print_unescaped('<p id="deleteallp">Delete All Saved tokens?<img id="deleteAllTokens" class="svg" src="' . image_path('core', 'actions/delete.svg') . '" alt="delete"/></p>');
		}
		print_unescaped('<input type="hidden" name="tokenId"  id="tokenId">');
		print_unescaped('</form>');
	}

/*
foreach ($_ as $key => $val) {

	print_unescaped('$key: ' . $key . '$val' . print_r($val, true));

}*/
print_unescaped('</div>');
}catch(\Exception $e){
	\OC::$server->getLogger()->error('Exception during FirebasePushNotifications Personal Settings display '.$e->getMessage().' '.$e->getTraceAsString());
	print_unescaped('<div><h3>FirebasePushNotifications: There was an error displaying firebase Settings: '.$e->getMessage().'</h3></div>');
}
