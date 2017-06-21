/**
 * Created by paolo on 09/05/2017.
 */
$(document).ready(function () {

	var userSettingsId = '#firebaseUserSettings';
	var adminOCMsgSpace = '#settings_activity';
	var userOCMsgSpace = '#firebase_user_settings_activity';
	var firebaseKeyMsgSpace = '#firebase_key_settings_activity';
	var tokenTestMsgSpace = '#firebase_test_message_activity';
	var tokenDeleteMsgSpace = "#opProgress";
	var basePostUrl = '/apps/firebasepushnotifications/';
	var adminFormId = '#firebasepushnotifications_settings';
	var firebaseKeyFormId = '#firebase_key_form';
	var tokenDeleteClass = '.tokenDelete';
	var tokenTestClass = '.tokenTest';
	var tokenDeleteAllId = '#deleteAllTokens';
	var tokenFormId = '#tokensForm';
	var lockToggleButton = '#lockToggle';
	var serverFirebaseKey = '#serverFirebaseKey';
	var firebaseKeyPlaceholder = undefined;

	function updateSettings () {
		OC.msg.startSaving(adminOCMsgSpace);
		var post = $(adminFormId).serialize();
		var dest = OC.generateUrl(basePostUrl+'appSettings');
		//alert('Post: ' + post + ' to: ' + dest);
		$.post(dest, post, function (response) {
			//alert('Finished: ' + response);
			OC.msg.finishedSuccess(adminOCMsgSpace, response.data.message);
		});
	}

	function updateUserSettings(){
		OC.msg.startSaving(userOCMsgSpace);
		var post = $(userSettingsId).serialize();
		var dest = OC.generateUrl(basePostUrl+'userSettings');
		$.post(dest,post,function(response) {
			OC.msg.finishedSuccess(userOCMsgSpace,response.data.message);
		});
	}

	function updateFirebaseCredentials(){
		if(confirm('Are you sure you want to change the Firebase Key?')==true){
			OC.msg.startSaving(firebaseKeyMsgSpace);
			var post = $(firebaseKeyFormId).serialize();
			var dest = OC.generateUrl(basePostUrl+'firebaseSettings');
			$.post(dest,post,function(response){
				OC.msg.finishedSuccess(firebaseKeyMsgSpace,response.data.message);
			});
		}else{
			$(serverFirebaseKey).val(firebaseKeyPlaceholder);
		}
		lockToggle();
	}
	function lockToggle(){
		var field = $(serverFirebaseKey);
		if(firebaseKeyPlaceholder === undefined){
			firebaseKeyPlaceholder = field.val();
		}
		field.prop('disabled',!field.prop('disabled'));
	}

	function deleteToken(){
		var tokenId = event.target.id;
		if(tokenId && confirm('Are you sure you want to delete this token?') == true){
			OC.msg.startSaving(tokenDeleteMsgSpace);
			$('#tokenId').val(tokenId);
			var post = $(tokenFormId).serialize();
			var dest = OC.generateUrl(basePostUrl + "deleteToken");
			$.post(dest, post, function (response) {
				OC.msg.finishedSuccess(tokenDeleteMsgSpace, response.data.message);
				if (response.data.removeRow) {
					$('#row' + response.data.removeRow).remove();
				}

			});
		}
	}

	function testToken () {
		var tokenId = event.target.id;
		if (tokenId && confirm('Send Test Message to token?') == true) {

			OC.msg.startSaving(tokenTestMsgSpace);
			$('#tokenId').val(tokenId);
			var post = $(tokenFormId).serialize();
			var dest = OC.generateUrl(basePostUrl+"testToken");
			$.post(dest,post,function(response){
				OC.msg.finishedSuccess(tokenDeleteMsgSpace,response.data.message);
				if(response.data.removeRow){
					$('#row'+response.data.removeRow).remove();
				}

			});
		}
	}

	function deleteAllTokens(){
		if(confirm('Are you sure you want to delete all your saved tokens?') == true){
			OC.msg.startSaving(tokenDeleteMsgSpace);
			var post = [];
			var dest = OC.generateUrl(basePostUrl+"deleteAllTokens");
			$.post(dest,post,function(response){
				OC.msg.finishedSuccess(tokenDeleteMsgSpace,response.data.message);
				if(response.data.removeRows){
					$('.tokenRow').remove();
				}
			});
		}
	}

	var tokenTestImgs = $(tokenTestClass);
	tokenTestImgs.click(testToken);

	var tokenImgs = $(tokenDeleteClass);
	tokenImgs.click(deleteToken);

	var tokenDeleteAllButton = "#deleteAllTokens";
	tokenDeleteAllButton = $(tokenDeleteAllButton);
	tokenDeleteAllButton.click(deleteAllTokens);


	var firebaseKeyChange = $(firebaseKeyFormId);
	if(firebaseKeyChange){
		firebaseKeyChange.find(lockToggleButton).click(lockToggle);
		firebaseKeyChange.find('input[type=text]').change(updateFirebaseCredentials);
	}

	var item = $(adminFormId);
	if(item){
		item.find('input[type=checkbox]').change(updateSettings);
	}

	var userSettings = $(userSettingsId);
	if(userSettings){
		userSettings.find('input[type=checkbox]').change(updateUserSettings);
	}
});