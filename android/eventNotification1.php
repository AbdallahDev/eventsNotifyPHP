<?php

//bellow is all the code related the push notification
//and i but it down here so i can get the variables from the form with conditions
//applied to it above, like the appointment time and event date.
//
//here i'll send notification with the event details
//bellow i'll select the committee name to send it with the notificaiton
include_once '../BLL/committees.php';
$committee = new committees();
$rs_committee = $committee->committee_get($_POST['committee']);
$row_committee = $rs_committee->fetch_assoc();
$committee_name = $row_committee['committee_name'];

//this api key for the firebase server, this api key has been taken from the firebase
//console to send push notification
//$registrationIds = ;
define('API_ACCESS_KEY', 'AAAAysijQG4:APA91bEVna5UC6cvLu8zFogm5m2F0GMCgK7LQhyaUpPuS840I6nCKIeytCtlvssjB6Vhsahc1cVZBnhtR73ZYD0lsa8urcdoqwc8ssXmwY-hJdFZgkV9UYIjGgxPL9yACi7FWBP0LOTk');

//bellow i'll select all the users tokens in the db to send them notifications
include_once '../BLL/notify_users.php';
$notify_user = new notify_user();
$rs_notify_user = $notify_user->get_all_users_token();
while ($row_notify_user = $rs_notify_user->fetch_assoc()) {
    //this variable to store all the needed information for the notification
    //like the event subject and date and time.
    $notificaiton_body = "<b>الموضوع:</b> " . $_POST['subject'] . "<br>"
            . "<br>"
            . "<b>التاريخ:</b> " . $_POST['event_date'] . "<br>"
            . "<b>الوقت:</b> " . $event_time;
    send_notification(nl2br($notificaiton_body), $committee_name
            , $row_notify_user['notify_user_token']);
}

//this function to send the push notification
function send_notification($notificaiton_body, $event, $token) {
#prep the bundle
    $msg = array
        (
        'bodymsg' => 'body msg',
        'titlemsg' => 'title msg',
    );

    $data = array(
        'body' => $notificaiton_body,
        'title' => $event,
    );

    $fields = array
        (
        'to' => $token,
        'notification' => $msg,
        'data' => $data
    );

    $headers = array
        (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

#Send Reponse To FireBase Server	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    echo $result;
    curl_close($ch);
}
