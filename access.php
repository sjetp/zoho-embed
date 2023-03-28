<?php
// Generate Code
if ($_GET) {
    $code= $_GET['code']; // from Authorize
    $_SESSION['code']=$code;
}
?>
<?php
if (is_null($_SESSION['refresh_token'])) {
    // Generate Refresh Token
    $Token_url = $accounts_url . "/oauth/v2/token?";
    $post_data = "code=" . $_SESSION['code'] . "&client_id=" . $ClienID . "&client_secret=" . $ClienSecret . "&redirect_uri=" . $Redirect_URI . "&grant_type=authorization_code";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response);

    foreach ($response as $key => $value) {
        if ($key == 'refresh_token'){}
            $_SESSION['refresh_token']=$value;
        if ($key == 'access_token')
            $_SESSION['access_token']=$value;
    }

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
$REFRESH_TOKEN = $_SESSION['refresh_token']; // Get From Step 2

$report_client_request = new ReportClient($CLIENT_ID, $CLIENT_SECRET, $REFRESH_TOKEN);

$uri = $report_client_request->getURI($EMAIL_ID, $DB_NAME, $TABLE_NAME);
$view_url = $report_client_request->getViewUrl($uri);
$_SESSION['$view_url']=$view_url;
?>