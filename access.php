<?php
require('config.php');
?>
<?php
// Generate Code
if ($_GET) {
    $code = $_GET['code']; // from Authorize
}
?>
<?php
// Generate Refresh Token
$Token_url = $accounts_url . "/oauth/v2/token?";
$post_data = "code=" . $code . "&client_id=" . $ClienID . "&client_secret=" . $ClienSecret . "&redirect_uri=" . $Redirect_URI . "&grant_type=authorization_code";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $Token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response);

$access_token = 0;
$refresh_token = 0;
foreach ($response as $key => $value) {
    if ($key == 'refresh_token')
        $refresh_token = $value;
    if ($key == 'access_token')
        $access_token = $value;
}
?>
<?php
//STEP 3: Generate View URL
require 'ReportClient.php';

$EMAIL_ID = "rohit@lets-viz.com"; //Email Address
$DB_NAME = "Sales Dashboard"; //Workspace Name
$TABLE_NAME = "Sales Dashboard";
$CLIENT_ID = $ClienID;
$CLIENT_SECRET = $ClienSecret;
$REFRESH_TOKEN = $refresh_token; // Get From Step 2

$report_client_request = new ReportClient($CLIENT_ID, $CLIENT_SECRET, $REFRESH_TOKEN);

$uri = $report_client_request->getURI($EMAIL_ID, $DB_NAME, $TABLE_NAME);
$view_url = $report_client_request->getViewUrl($uri);
?>
