<?php
require('config.php');
$refresh_token=$_GET['token'];
// Generate Refresh Token
$Token_url = $accounts_url . "oauth/v2/token/revoke?";
$post_data = "token=" . $refresh_token;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $Token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
?>