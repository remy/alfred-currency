<?php
require_once('workflows.php');
$wf = new Workflows();

function getRow($currency_from, $currency_to, $amount){
  global $wf;

  // build URL
  $url = "http://api.fixer.io/latest?base=" . strtoupper($currency_from) . "&symbols=" . strtoupper($currency_to);

  // get exchange rate
  $data_json = file_get_contents($url);
  $data = json_decode($data_json);

  // Error occurec. Probabl invalid currency
  if(isset($data->err)){
    $subtext = "Supported currencies are: DKK, EUR, USD, SEK, GBP, CHF, AUD, NOK";
    $wf->result(time(), $data->err, "Invalid currency", $subtext, 'icon.png');

  // No problems
  }else{
    // print_r($data);
    $exchange_rate = $data->rates->$currency_to;

    $clipboard = round($exchange_rate * $amount, 1);
    $text = round($exchange_rate * $amount, 1) . " " . $currency_to;
    $sub_text = $currency_from . ' to ' . $currency_to;

    $wf->result(time(), $clipboard, $text, $sub_text, 'icon.png');
  }
}

function getWaitingRow(){
  global $wf;
  $wf->result(time(), "Waiting for input", "Waiting for input...", "Type in like: 50USD to EUR", 'icon.png');
}

function getResult($query, $default_currency){
  global $wf;

  // Replace symbols
  $query = str_replace(
    array("€", "$", "£"),
    array("EUR", "USD", "GBP"),
    $query
  );

  // uppercase
  $query = strtoupper($query);

  // strip " to "
  $query = str_replace(" TO ", " ", $query);
  $query = str_replace(" IN ", " ", $query);

  // trim
  $query = trim($query);

  // print_r('>>> query');
  // print_r($query);

  // parse query
  preg_match("/^(\d+)\s?([A-Z]{3})\s?([A-Z]{3})?$/", $query, $matches);

  // print_r('>>> matches');
  // print_r($matches);
  if(!empty($matches)){

    $amount = $matches[1];
    $currency_from = $matches[2];
    $currency_to = isset($matches[3]) ? $matches[3] : $default_currency;

    // Initial row
    getRow($currency_from, $currency_to, $amount);

  }else{
    getWaitingRow();
  }

  return $wf->toxml();
}

// echo getResult("40usd $");
