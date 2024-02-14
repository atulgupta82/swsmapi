<!DOCTYPE html>
<html>

<head>
  <title>Radio Buttons and Forms</title>
  <script>
    function showForm(radioId) {
      const forms = document.querySelectorAll('form');
      forms.forEach(form => form.style.display = 'none'); // Hide all forms first

      const selectedForm = document.getElementById(`form-${radioId}`);
      if (selectedForm) {
        selectedForm.style.display = 'block'; // Show only the corresponding form
      }
    }
  </script>
</head>

<body>
  <!-- for gst number-->
  <input type="radio" id="gst" name="choice" onclick="showForm('gst')">
  <label for="gst">GST</label><br>
  <form id="form-gst" style="display: none;" method='POST'>
    <input type="text" name="gst_number" placeholder="Enter GST Number"><br>
    <button type="submit" name='submitbtn'>Verify</button>
    <input type="hidden" name="hidden_text" value="gst">
  </form>

  <!-- for pan number-->
  <input type="radio" id="pan" name="choice" onclick="showForm('pan')">
  <label for="pan">PAN</label><br>
  <form id="form-pan" style="display: none;" method='POST'>
    <input type="name" name="pan_number" placeholder="Enter PAN Number"><br>
    <button type="submit" name='submitbtn'>Verify</button>
    <input type="hidden" name="hidden_text" value="pan">
  </form>

  <!-- for bank account number-->
  <input type="radio" id="account" name="choice" onclick="showForm('account')">
  <label for="account">Account</label><br>
  <form id="form-account" style="display: none;" method='POST'>
    <input type="number" name="account_number" placeholder="Enter Account Number"><br>
    <input type="name" name="ifsc_code" placeholder="Enter ifsc code"><br>
    <button type="submit" name='submitbtn'>Verify</button>
    <input type="hidden" name="hidden_text" value="account">
  </form>
</body>

</html>



<?php

if (isset($_POST['submitbtn'])) {
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $verification_type = $_POST['hidden_text'];
    $api_key = "AP472426";
    $secret_key = "BEF8C406E0";
  }
}


if ($verification_type == 'gst') {
  $gst_number = $_POST['gst_number'];
  gst_verification($gst_number, $api_key, $secret_key);
} elseif ($verification_type == 'pan') {
  $pan_number = $_POST['pan_number'];
  pan_verification($pan_number, $api_key, $secret_key);
} elseif ($verification_type == 'account') {
  $account_number = $_POST['account_number'];
  $ifsc = $_POST['ifsc_code'];
  account_verification($account_number, $ifsc, $api_key, $secret_key);
}

// generating random transaction id
function txnid_random() {
  $randomNumber = rand(1, 9); 
  $randomNumber .= str_pad(rand(0, 999999999999999999), 30, '0', STR_PAD_LEFT);
  return $randomNumber;
}


// gst verification
function gst_verification($gst_number, $api_key, $secret_key)
{
    // API endpoint URL
  $url = 'https://cyrusrecharge.in/api/total-kyc.aspx';

  $txn_id = txnid_random();

  $data = array(
    'merchantId' => $api_key,
    'merchantKey' => $secret_key,
    'id_number' => $gst_number,
    'type' => 'GST VERIFICATION',
    'txnid' => $txn_id
  );

    // Process the API response
    $response = send_curl($data, $url);
    $response_data = json_decode($response, true);
    if($response_data['statuscode'] == 103){
      gst_verification($gst_number, $api_key, $secret_key);
      }
    else{
       echo $response;
    }
}




// pan card verification
function pan_verification($pan_number, $api_key, $secret_key)
{
    // API endpoint URL
    $url = 'https://cyrusrecharge.in/api/total-kyc.aspx';

    $txn_id = txnid_random();
  
    $data = array(
      'merchantId' => $api_key,
      'merchantKey' => $secret_key,
      'panNumber' => $pan_number,
      'type' => 'PANCARD',
      'txnid' => $txn_id
    );
  
    $response = send_curl($data, $url);
    $response_data = json_decode($response, true);
    if($response_data['statuscode'] == 103){
      pan_verification($pan_number, $api_key, $secret_key);
      }
    else{
       echo $response;
    }
}

function account_verification($account_number, $ifsc, $api_key, $secret_key)
{
    // API endpoint URL
    $url = 'https://cyrusrecharge.in/api/total-kyc.aspx';

    $txn_id = txnid_random();
  
    $data = array(
      'merchantId' => $api_key,
      'merchantKey' => $secret_key,
      'Account' => $account_number,
      'Ifsc' => $ifsc,
      'type' => 'ACCOUNT VERIFICATION',
      'txnid' => $txn_id
    );

    // Process the API response
    $response = send_curl($data, $url);
    $response_data = json_decode($response, true);
    if($response_data['statuscode'] != 103){
      account_verification($account_number, $ifsc, $api_key, $secret_key);
      }
    else{
       echo $response;
    }

}

// sending api calls
function send_curl($data, $url){
  // Initialize a cURL session
  $curl = curl_init();

  // Set cURL options
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_POST => true,  // Use POST method
    CURLOPT_POSTFIELDS => json_encode($data),  // Send data as JSON
    CURLOPT_RETURNTRANSFER => true,  // Return response as string
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'  // Set content type
    )
  ));

    // Execute the cURL request
    $response = curl_exec($curl);

      // Close the cURL session
    curl_close($curl);

    return $response;
}

?>