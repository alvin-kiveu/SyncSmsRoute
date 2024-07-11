<?php
$rawData = file_get_contents('php://input');
parse_str($rawData, $parsedData);

if ($parsedData['from'] == "UMS_SMS" || $parsedData['from'] == "MPESA") {
  $message = urldecode($parsedData['message']);
  preg_match('/(SGB[0-9A-Z]+)/', $message, $transactionIdMatch);
  preg_match('/Ksh([0-9,.]+)/', $message, $amountMatch);
  preg_match('/from\s+([A-Z\s]+)\s+\d{10}/', $message, $nameMatch);
  preg_match('/(\d{10})/', $message, $phoneNumberMatch);
  preg_match('/on\s+(\d{1,2}\/\d{1,2}\/\d{2}\s+at\s+\d{1,2}:\d{2}\s+(?:AM|PM))/', $message, $timeMatch);
  preg_match('/balance\s+is\s+Ksh([0-9,.]+)/', $message, $balanceMatch);


  $transactionId = $transactionIdMatch[1];
  $amount = $amountMatch[1];
  $name = trim($nameMatch[1]);
  $phoneNumber = $phoneNumberMatch[0];
  $time = $timeMatch[1];
  $balance = $balanceMatch[1];

  $logMpesaFile = "MpesaSMSData.txt";
  $logmPESA = fopen($logMpesaFile, "a");

  $formattedMpesaData = "----------------------------------\n";
  $formattedMpesaData .= "Transaction ID: $transactionId\n";
  $formattedMpesaData .= "Amount: Ksh$amount\n";
  $formattedMpesaData .= "Name: $name\n";
  $formattedMpesaData .= "Phone Number: $phoneNumber\n";
  $formattedMpesaData .= "Time: $time\n";
  $formattedMpesaData .= "Balance: Ksh$balance\n";
  $formattedMpesaData .= "----------------------------------\n";

  // Write the formatted data to the log file
  fwrite($logmPESA, $formattedMpesaData);

  $logFile = "SMS.txt";
  $log = fopen($logFile, "a");
  $formattedData = "----------------------------------\n";
  $formattedData .= "Timestamp: " . date('Y-m-d H:i:s', $parsedData['timestamp'] / 1000) . "\n";
  $formattedData .= "Message Type: " . $parsedData['message_type'] . "\n";
  $formattedData .= "Message: " . $message . "\n";
  $formattedData .= "Action: " . $parsedData['action'] . "\n";
  $formattedData .= "From: " . $parsedData['from'] . "\n";
  $formattedData .= "Phone Number: " . $parsedData['phone_number'] . "\n";
  $formattedData .= "Battery: " . $parsedData['battery'] . "%\n";
  $formattedData .= "Network: " . $parsedData['network'] . "\n";
  $formattedData .= "----------------------------------\n";
  fwrite($log, $formattedData);
  fclose($log);
  echo json_encode(["status" => "success", "message" => "SMS data stored"]);
} else {
  echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
