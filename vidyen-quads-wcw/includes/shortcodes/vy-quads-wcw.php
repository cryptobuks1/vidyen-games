<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*** RNG Quad Game ***/
//Here we go. Will be AJAX to simply click and bet points on wheel spin rewards.
//Shortcode etc. I'm not sure if this should be a function per se, but I feel that this should go in base. As its damn interesting concept.
//Going to copy the Raffle format except there will be no PvP but PvE (well player vs house). I kind of forgot that we can just... Well... Print points...
//Players betting on the house would be interesting but not really needed.

/*** The RNG Quads Function ***/

function vy_quads_wcw_func( $atts )
{

  $atts = shortcode_atts(
    array(
        'pid' => 1,
        'uid' => '0',
        'raw' => FALSE,
        'decimal' => 0,
        'betbase' => 10,
        'betmulti' => 10,
        'font' => 40,
    ), $atts, 'vy-quads-wcw' );

  //Login check.
  if ( ! is_user_logged_in() )
  {
      return;
  }

  //Adding a nonce to the post
  $vyps_nonce_check = wp_create_nonce( 'vy-quads-wcw-nonce-quads' );

  $VYPS_power_url = plugins_url( 'images/', dirname(__FILE__) ) . 'powered_by_vyps.png'; //Well it should work out.
  $VYPS_power_row = "<div align=\"left\">Powered by <a href=\"https://wordpress.org/plugins/vidyen-point-system-vyps/\" target=\"_blank\"><img src=\"$VYPS_power_url\" alt=\"Powered by VYPS\"></a></div>";

  //Procheck here. Do not forget the ==
  if (vyps_procheck_func($atts) == 1)
  {
    $VYPS_power_row = ''; //No branding if procheck is correct.
  }

  //Get the url for the Quads js
  $vyps_quads_jquery_folder_url = plugins_url( 'js/jquery/', __FILE__ );
  $vyps_quads_jquery_folder_url = str_replace('shortcodes/', '', $vyps_quads_jquery_folder_url); //having to reomove the folder depending on where you plugins might happen to be
  $vyps_quads_js_url =  $vyps_quads_jquery_folder_url . 'jquery-1.8.3.min.js';

  $starting_balance_html = "<span class=\"woo-wallet-icon-wallet\"></span>" . ' ' . vy_quads_wcw_bal_func($atts);

  //Font size. Not really that important, but someone might complain
  $font_size = 'font-size:' . intval($atts['font']) . 'px;';

  //Setting the intervals of bets
  $bet_base = intval($atts['betbase']); //no need to have ints with woowallet
  $bet_multi = intval($atts['betmulti']);
  $bet_base_display = $bet_base / 100;

  //This is the passed number
  $bet_first = $bet_base;
  $bet_second = ($bet_base * $bet_multi);
  $bet_third = ($bet_base * $bet_multi * $bet_multi);
  $bet_fourth = ($bet_base * $bet_multi * $bet_multi * $bet_multi);

  //The shortcode should allow users to set the intervals of the bets. Honestly, I think this is ok for ajax to pass as the outcome ratios are not determined by shortcode (and I don't think they will ever be directly. Perhaps an SQL pass will have to be done)
  $bet_first_display = number_format($bet_base_display, 2);
  $bet_second_display = number_format($bet_base_display * $bet_multi, 2);
  $bet_third_display = number_format($bet_base_display * $bet_multi * $bet_multi, 2);
  $bet_fourth_display = number_format($bet_base_display * $bet_multi * $bet_multi * $bet_multi, 2);

  $icon_url = "<span class=\"woo-wallet-icon-wallet\"></span>";

  $vy_quads_wcw_html_output = "
    <script>
      var randomtime = setInterval(timeframe, 36);
      function timeframe() {
        document.getElementById('animated_number_output').innerHTML = Math.floor(Math.random()*10000) + Math.floor(Math.random()*1000) + Math.floor(Math.random()*100) + Math.floor(Math.random()*10);
      }

      function gettherng(multi) {
        if (multi === undefined){
          multi = 0;
        }
        document.getElementById(\"animated_number_output\").style.display = 'block'; // disable button
        document.getElementById(\"number_output\").style.display = 'none'; // enable button
        document.getElementById(\"results_div\").style.display = 'none'; // enable button
        jQuery(document).ready(function($) {
         var data = {
           'action': 'vy_run_quads_wcw_action',
           'multicheck': multi,
           'vypsnoncepost': '$vyps_nonce_check',
         };
         // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
         jQuery.post(ajaxurl, data, function(response) {
           output_response = JSON.parse(response);
           document.getElementById('number_output').innerHTML = output_response.full_numbers;
           document.getElementById('current_balance').innerHTML = output_response.post_balance;
           document.getElementById('response_text').innerHTML = output_response.response_text  + ' - Earned: ';
           document.getElementById('reward_balance').innerHTML = output_response.reward;
           document.getElementById(\"animated_number_output\").style.display = 'none'; // disable button
           document.getElementById(\"number_output\").style.display = 'block'; // enable button
           document.getElementById(\"results_div\").style.display = 'block'; // enable button
         });
        });
      }

      function spin_numbers() {
        randomtime = setInterval(timeframe, 36);
      }

    </script>
    $VYPS_power_row
    <div align=\"center\"><span id=\"animated_number_output\" style=\"display:block; $font_size\">0000</span></div>
    <div align=\"center\"><span id=\"number_output\" style=\"display:none; $font_size\">0000</span></div>
    <div id=\"bet_action1\" align=\"center\" style=\"width:100%;\"><button onclick=\"gettherng($bet_first)\" style=\"width:50%;\">$icon_url $$bet_first_display</button><button onclick=\"gettherng($bet_second)\" style=\"width:50%;\">$icon_url $$bet_second_display</button></div>
    <div id=\"bet_action2\" align=\"center\" style=\"width:100%;\"><button onclick=\"gettherng($bet_third)\" style=\"width:50%;\">$icon_url $$bet_third_display</button><button onclick=\"gettherng($bet_fourth)\" style=\"width:50%;\">$icon_url $$bet_fourth_display</button></div>
    <div align=\"center\"><span>Balance: </span><span id=\"current_balance\"> $starting_balance_html</span></div>
    <div id=\"results_div\" align=\"center\" style=\"display:none;\"><span id=\"response_text\"></span><span>$icon_url </span><span id=\"reward_balance\">$0.00</span></div>
    ";

    return $vy_quads_wcw_html_output;
}

/*** Short Code Name for RNG quads ***/

add_shortcode( 'vy-quads-wcw', 'vy_quads_wcw_func');

/*** PHP Functions to handle AJAX request***/

// register the ajax action for authenticated users
add_action('wp_ajax_vy_run_quads_wcw_action', 'vy_run_quads_wcw_action');

// handle the ajax request
function vy_run_quads_wcw_action()
{

  global $wpdb; // this is how you get access to the database

  //This should cause a die. If the nonce fails.
  check_ajax_referer( 'vy-quads-wcw-nonce-quads', 'vypsnoncepost' );

  //If its not clear, this is actually needed an should be left alone. In theory, user could hack a post somehow getting around the vypsnonce, but it just bets what its given and validates.
  $incoming_multiplier = intval($_POST['multicheck']);
  $bet_cost = floatval($incoming_multiplier) / 100;

  // Shortcode additions.
  $atts = shortcode_atts(
		array(
				'outputamount' => $bet_cost,
        'refer' => 0,
				'to_user_id' => get_current_user_id(),
        'comment' => '',
    		'reason' => 'QUADBET',
				'btn_name' => 'QUADRUN',
        'raw' => FALSE,
        'cost' => 1,
        'pid' => $incoming_pointid_get,
        'firstid' => $incoming_pointid_get,
        'firstamount' => $bet_cost,
    ), $atts, 'vy-quads-wcw' );

  //Get current balance.
  $pre_current_user_balance = floatval(vy_quads_wcw_bal_func($atts));

  if ($bet_cost >= $pre_current_user_balance)
  {
    //Not enough to play!
    $post_current_user_balance = floatval(vy_quads_wcw_bal_func($atts));

    $response_text = "NOT ENOUGH POINTS!";

    $rng_numbers_combined = $response_text;

    $reward_amount = 0;

    $rng_array_server_response = array(
        'first' => $digit_first,
        'second' => $digit_second,
        'third' => $digit_third,
        'fourth' => $digit_fourth,
        'full_numbers' => $rng_numbers_combined,
        'response_text' => $response_text,
        'pre_balance' => $pre_current_user_balance,
        'post_balance' => $post_current_user_balance,
        'reward' => $reward_amount,
    );

    //Get the random 4 digit number. Just testing... will get a better check later.
    //$rng_server_response = $digit_first . $digit_second . $digit_third . $digit_fourth . $response_text;

    echo json_encode($rng_array_server_response);

    wp_die(); // this is required to terminate immediately and return a proper response
  }

  //Deduct. I figure there is a check when need to run.
  $deduct_results = vy_quads_wcw_debit_func($atts);

  $digit_first = mt_rand(0, 9);
  $digit_second = mt_rand(0, 9);
  $digit_third = mt_rand(0, 9);
  $digit_fourth = mt_rand(0, 9);

  //Some math Mathmatics. If A = B and C = D and A = D, then B = C

  if (($digit_first == $digit_second) AND ($digit_third == $digit_fourth) AND (($digit_first == $digit_fourth)))
  {
    //WE got quads
    $response_text = "QUADS";
    $reward_amount = floatval($bet_cost * 10 ); //Ok. It took me a while to figure out fair odds. Basically this is = 1/( (1/10) * (1/10) * (1/10) * (1/10) ) odds per point spend of winning or 0.0001% of getting quads per roll
    $rng_numbers_combined = '<b>' . $digit_first . $digit_second . $digit_third . $digit_fourth . '</b>'; //Bolding for end user
  }
  elseif (($digit_first == $digit_second) AND ($digit_first == $digit_third))
  {
    //We got trips on first 3
    $response_text = "TRIPS";
    $reward_amount = floatval($bet_cost * 5); //Or =1/((1/10)*(1/10)*(1/10)+(1/10)*(1/10)*(1/10))
    $rng_numbers_combined = '<b>' . $digit_first . $digit_second . $digit_third . '</b>' . $digit_fourth; //First three bold
  }
  elseif (($digit_second == $digit_third) AND ($digit_second == $digit_fourth))
  {
    //trips on last 3
    $response_text = "TRIPS";
    $reward_amount = floatval($bet_cost * 5);
    $rng_numbers_combined = $digit_first . '<b>' . $digit_second . $digit_third . $digit_fourth . '</b>'; //Last three bold
  }
  elseif ($digit_first == $digit_second)
  {
    //dubs on first 2
    $response_text = "DUBS";
    $reward_amount = floatval($bet_cost * 2.317443396445042); //Ok. I wish I could post how i got this number, but had a discussion on a discord about the fair payout on this.
    $rng_numbers_combined = '<b>' . $digit_first . $digit_second . '</b>' . $digit_third . $digit_fourth; //First two
  }
  elseif ($digit_second == $digit_third)
  {
    //dubs on  middle 2
    $response_text = "DUBS";
    $reward_amount = floatval($bet_cost * 2.317443396445042);
    $rng_numbers_combined = $digit_first . '<b>' . $digit_second . $digit_third . '</b>' . $digit_fourth; //Middle two
  }
  elseif ($digit_third == $digit_fourth)
  {
    //dubs on last 2
    $response_text = "DUBS";
    $reward_amount = floatval($bet_cost * 2.317443396445042);
    $rng_numbers_combined = $digit_first . $digit_second . '<b>' . $digit_third . $digit_fourth . '</b>'; //Last two
  }
  elseif ($digit_first == $digit_second AND $digit_third == $digit_fourth )
  {
    //ddouble dubs
    $response_text = "DOUBLEDUBS";
    $reward_amount = floatval($bet_cost * 5); //Same as trips but half as unlikely as you pairs although almost statitically the same do not require the same card. A statistics major might want to argue this with me though.
    $rng_numbers_combined = $digit_first . $digit_second . '<b>' . $digit_third . $digit_fourth . '</b>'; //Last two
  }
  else
  {
      //YOU GET NOTHING!
      $response_text = "FAIL";
      $reward_amount = 0;
      $rng_numbers_combined = $digit_first . $digit_second . $digit_third . $digit_fourth; //Fail. Has no bolding.
  }

  //Well if they won. They should get something.
  if ($reward_amount > 0  AND $deduct_results == 1)
  {
    $atts['reason'] = $response_text;
    $atts['outputid'] = $incoming_pointid_get;
    $atts['outputamount'] = floatval($reward_amount); //Yeah its going to round so what? NO DECIMALS!
    vy_quads_wcw_credit_func($atts); //add funciton
  }

  $post_current_user_balance = vy_quads_wcw_bal_func($atts); //BALANCE!

  $rng_array_server_response = array(
      'first' => $digit_first,
      'second' => $digit_second,
      'third' => $digit_third,
      'fourth' => $digit_fourth,
      'full_numbers' => $rng_numbers_combined,
      'response_text' => $response_text,
      'pre_balance' => $pre_current_user_balance,
      'post_balance' => $post_current_user_balance,
      'reward' => $reward_amount,
  );

  //Get the random 4 digit number. Just testing... will get a better check later.
  //$rng_server_response = $digit_first . $digit_second . $digit_third . $digit_fourth . $response_text;

  echo json_encode($rng_array_server_response);

  wp_die(); // this is required to terminate immediately and return a proper response
}
