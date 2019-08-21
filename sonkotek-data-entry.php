<?php
/*
*Plugin Name: Sonkotek Data Entry
*Plugin URI: https://sonkotek.com
*Description: A data entry plugin to record and display data
*Version: 0.0.1
*Author: Novi Sonko <novisonko@sonkotek.com>
*Author URI: https://novisonko.com
*@package Sonkotek\DE
*@copyright Copyright (c) 2017, Sonkotek Systems
*/
namespace Sonkotek\DE;

const USER_META_KEY= 'lead_user_info';
const PAGESIZE= 25;
const DATA_ENTRY_FORM_PAGE= "user_data";
const PLUGIN_PAGE= 'sonkotek-data-entry/sonkotek-data-entry.php';

/**
*Activation
*/
/**
*Admin Menu
*/
function register_menu_page() {
  add_menu_page( "Data entry", "Sonkotek data entry", "manage_options", __FILE__, "\Sonkotek\DE\list_users");
}
add_action( 'admin_menu', '\Sonkotek\DE\register_menu_page' );
/**
Add style
*/
wp_register_style( '\Sonkotek\DE', plugin_dir_url(__FILE__).'/css/style.css' );
wp_enqueue_style( '\Sonkotek\DE' );
/**
*List users with data
*/
function list_users ()
{
  global $wpdb;

  $offset= 0;
  $use_paging= false;

  if (!empty($_GET['sde_pg']) && $_GET['sde_pg'] > 0) {
    $offset= PAGESIZE * ($_GET['sde_pg']-1);
  }

  $get_users_info_query= "SELECT * FROM wp_users u INNER JOIN wp_usermeta um ON u.ID=um.user_id WHERE um.meta_key = '%s' ORDER BY u.ID DESC  %s;";

  $results = $wpdb->get_results( sprintf($get_users_info_query, USER_META_KEY, ''));

  // no result
  if ($wpdb->num_rows === 0):
  ?>
  <p class="sde-result">
    No record to show...
  </p>
  <?php
  //end here
  return;
  endif;

  // more than pagesize
  if ($wpdb->num_rows > PAGESIZE)
  {
    $total= $wpdb->num_rows;
    $use_paging= true;

    if (!empty($_GET['sde_pg'])
    && $_GET['sde_pg'] > 0
    && $offset < $wpdb->num_rows
    ) {
      $get_users_info_query= sprintf($get_users_info_query, USER_META_KEY, 'LIMIT '.(PAGESIZE+$offset).' OFFSET '.$offset);
      $results = $wpdb->get_results( $get_users_info_query );
    }
    else {
      $get_users_info_query= sprintf( $get_users_info_query, USER_META_KEY, 'LIMIT '.PAGESIZE );
      $results = $wpdb->get_results( $get_users_info_query );
    }
  }
?>

<table class="sde-table">
  <?php
  $counter= 0;

  foreach ($results as $user) {
    if (0 === $counter)
    {
      echo '<tr><th>user ID</th><th>Username</th>';

      $fields_keys= get_option('sde_fields_keys');
      $fields_keys= !empty($fields_keys) ? $fields_keys : [];
      for ($i=0; $i<5; $i++) {
        if (isset($fields_keys[$i])) {
          echo '<th>'.ucfirst(str_replace('_', ' ', $fields_keys[$i])).'</th>';
        }
        else {
          echo '<th>Field'.($i+1).'</th>';
        }
      }
      echo '</tr>';
      $counter++;
    }

    echo '<tr><td>'.$user->ID.'</td><td>'.$user->user_login.'</td>';
    $fields= unserialize($user->meta_value);
    foreach($fields as $field){
    echo '<td>'.$field.'</td>';
    }
    echo '</tr>';
  }
  ?>
</table>
<?php
  if ($use_paging && $total > PAGESIZE)
  {
    $numpages= floor($total/PAGESIZE);
    if ($numpages > 1)
    {
      echo '<div class="paging">';
      for ($i=1; $i <= $numpages; $i++)
      {
        echo '<a href="'.admin_url('admin.php?page='.PLUGIN_PAGE).'&sde_pg='.$i.'">'.$i.'</a>';
          if ($i < $numpages) {
            echo ' | ';
          }
      }
      echo '<div>';
    }
  }
}

/**
*Function data entry form
*/
function display_form (array $atts=[])
{

  \Sonkotek\DE\add_ajax_scripts();

  $fields= $labels= $custom_fields=[];

  // get form fields
  if (isset($atts['fields'])) {
    $custom_fields= explode(',', $atts['fields']);
  }

  for ($i=0; $i<6; $i++) {
    if (isset($custom_fields[$i])) {
      $fields[$i]= str_replace(' ', '_', trim($custom_fields[$i]));
      $labels[$i]= trim($custom_fields[$i]);
    }
    else {
      $fields[$i]= $labels[$i]= 'field'.($i+1);
    }
  }
  // save fields
  set_transient('sde_form_fields', $fields, 24*HOUR_IN_SECONDS);
  // get values
  $data= get_usermeta(get_current_user_id(), USER_META_KEY);

  // get field values from database
  $values=[];
    foreach($fields as $field){
      if (isset($data[$field])){
        $values[$field]= $data[$field];
      }
      else {
        $values[$field]='';
      }
    }
?>
<form id="sde-form-1" class="sde-form">
      <div class="sde-form-msg">
      </div>
    <div>
    <label for="field1"><?php echo ucfirst($labels[0]); ?></label>
    <input type="text" name="<?php echo $fields[0]; ?>" value="<?php echo $values[$fields[0]]; ?>" id="field1">
  </div>
  <div>
    <label for="field2"><?php echo ucfirst($labels[1]); ?></label>
    <input type="text" name="<?php echo $fields[1]; ?>" value="<?php echo $values[$fields[1]]; ?>" id="field2">
  </div>
  <div>
    <label for="field3"><?php echo ucfirst($labels[2]); ?></label>
    <input type="text" name="<?php echo $fields[2]; ?>" value="<?php echo $values[$fields[2]]; ?>" id="field3">
  </div>
  <div>
    <label for="field4"><?php echo ucfirst($labels[3]); ?></label>
    <input type="text" name="<?php echo $fields[3]; ?>" value="<?php echo $values[$fields[3]]; ?>" id="field4">
  </div>
  <div>
    <label for="field5"><?php echo ucfirst($labels[4]); ?></label>
    <input type="text" name="<?php echo $fields[4]; ?>" value="<?php echo $values[$fields[4]]; ?>" id="field5">
  </div>
  <div>
    <input class="btn" type="submit" value="Submit">
  </div>
</form>
<?php
}

//Add form shortcode
add_shortcode('sde_form', '\Sonkotek\DE\display_form');
/**
*Function process data entry form
*/
function process_form ()
{
  // save messages to transient
  $msg= '';
  $status= 404;

  if (!empty($_POST['action'])
    && $_POST['action'] === 'sde_save_form'){

    $fields_keys= get_transient('sde_form_fields');

    $fields=[];

    if (!is_array($fields_keys) || count($fields_keys) === 0){
      $msg= '<p class="error">Error! Fields not found...</p>';
    }

    $submitted= isset($_POST['submitted']) ? $_POST['submitted'] : [];

    for ($i=0; $i<6; $i++){
      if (!empty($submitted[$fields_keys[$i]])) {
        $fields[$fields_keys[$i]]= $submitted[$fields_keys[$i]];
      }
    }

    if (count($fields) > 0){
      update_usermeta(get_current_user_id(), USER_META_KEY, $fields);
      $msg= '<p class="confirm">The fields have been saved, thank you</p>';
      $status= 200;
    }

    update_option('sde_fields_keys', $fields_keys);
  }
  else {
    $msg= '<p class="error">Error! Something went wrong...</p>';
    $status= 503;
  }

  header("HTTP/1.1 ".$status);
  echo json_encode(['msg' => $msg]);
  exit;

}
add_action( 'wp_ajax_sde_save_form', '\Sonkotek\DE\process_form' );

/**
*Add ajax scripts to frontend for this plugin
*/
function add_ajax_scripts()
{
  wp_register_script('Sonkotek_de_js', plugin_dir_url(__FILE__).'js/sonkotek-de.js', array(), 'sonkotek_de_0.0.1');
	wp_enqueue_script('Sonkotek_de_js');

	$config_array = array(
	'ajaxURL' => admin_url('admin-ajax.php'),
	'ajaxAction' => 'sde_save_form',
	'ajaxNonce' => wp_create_nonce('sde_save_form'),
	);

	wp_localize_script('Sonkotek_de_js', 'sonkotekSdeSaveForm', $config_array);
}
