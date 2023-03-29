<?php
require('config.php');

// code is valid for 10 mins (as selected) used to create refresh token for the scope
$code="1000.dc2536115c3452da0171e6a69202b04d.71ae7a4d455159ee6b9462cbf8b73966";
$refresh_token=NULL
?>
<?php
if (is_null($refresh_token)) {
    // Generate Refresh Token
    $Token_url = $accounts_url . "/oauth/v2/token?";
    $post_data = "code=" . $code . "&client_id=" . $ClienID . "&client_secret=" . $ClienSecret . "&grant_type=authorization_code";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response);

    foreach ($response as $key => $value) {
        if ($key == 'refresh_token')
            $refresh_token = $value;
    }
}
var_dump($response);
?>