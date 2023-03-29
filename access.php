<?php
require('config.php');
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
