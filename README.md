Firebase Push Notifications
==
//Logo

Firebase Push Notifications creates Push Notifications based on file/share activities and sends them to NextCloud mobile using Firebase Notifcations. 

### Features
- Receive Notifications for file related events on NextCloud
- Choose which Notifications should be sent (Admin and User level settings are present, for better granularity)
- Users can manage their registered tokens and settings or turn off Notifications if they don't want them

### A word of warning - this app is still currently in beta
This app still requires the user to have either an experimental version of the NextCloud mobile app, or ot manually register a device for Firebase Notifications.
This app also requires a server side Firebase Key to be supplied in order to send notifications, this can be set by an admin in the corresponding section of the NextCloud admin settings.


### Installation

clone this repository in your nextcloud/apps folder under the "firebasepushnotifications" path
example starting at your NextCloud servers apps path, ie: /var/www/nextcloud/apps/
```sh
    $ cd /var/www/your_nextcloud_path_here/apps/
    $ git clone https://github.com/LiveBoxCloud/nextcloud-firebasepushnotifications.git firebasepushnotifications
```

Log in to your nextcloud installation as an admin and Enable the app.  (Please do report any errors or problems you encounter, we will gladly fix them),

### Configuration

Still logged in as an admin, go to **Settings** and then **Firebase Push Notifications** you have to add a Firebase Server key (to enable the field just click the **lock** icon)

You will also notice that you can enable or disable push notifications globally or for given messages from here (If for example you are interested in receiving notifications related to shared content, but not when it is merely a move operation for example).

## Registering Devices 
FirebasePushNotifications exposes REST API to allow token management operations
devices and applications registering for push messages have to perform the following calls:

The API currently requires the caller to be authenticated or to supply authentication information to the NextCloud server in the process. 
#### Registration
##### Request:
URL : your_nextcloud_path/apps/firebasepushnotifications/registerToken
parameters: 
- token (your application Firebase token)
- resource (a unique string identifying the device)
- deviceType (whether registering an iOS or Android device , fell free to ask if you need to support for other platforms)
- locale (currently supports EN_en, IT_it, and ES_es. more to come and per request)

##### Response:
*Status* : __200__ (__HTTP_OK__) on success or __400__ (__HTTP_BAD_REQUEST__) on failure  
*Json Repsonse*: containing the following fields  
 *result*: success/error  
 *extra*: text message with details  
 And optionally containing:  
 *error* : error message  
 *user_auth* : for OC / NC authentication problems  
 *token* : for problems with the token field  
 *unknown* : other unexpected conditions  
 

#### Deregistration
##### Request:
URL : your_nextcloud_path/apps/firebasepushnotifications/unregisterToken  s
- token (the token to be unregistered)
##### Response:
same fields as the registration request


License
----
GNU AGPL version 3 or any later version
Copyright (c) 2017, **LiveBox** (_support@liveboxcloud.com_)

