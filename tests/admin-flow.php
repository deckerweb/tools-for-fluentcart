<?php
use Deckerweb\ToolsForFluentCart\Admin;
require_once ABSPATH.'wp-admin/includes/plugin.php';
$GLOBALS['tffc_admin_checks']=0;
function admin_check($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_admin_checks'];echo "PASS $label\n";}
$key=plugin_basename(TFFC_FILE);
admin_check(get_plugin_data(TFFC_FILE,false,false)['RequiresPlugins']==='','Native hard dependency no longer prevents host activation');
admin_check(validate_plugin_requirements($key)===true,'Core activation requirements accept the host');
$old_menu=$GLOBALS['menu']??[];$old_submenu=$GLOBALS['submenu']??[];
$GLOBALS['admin_page_hooks']['fluent-cart']='fluentcart';
$GLOBALS['menu']=[['FluentCart','manage_options','fluent-cart']];$GLOBALS['submenu']=[];
Admin::menu();
admin_check(in_array('tools-for-fluentcart',array_column($GLOBALS['submenu']['fluent-cart']??[],2),true),'Registered settings page is under FluentCart');
admin_check(str_contains(Admin::settings_url(),'/wp-admin/admin.php?page=tools-for-fluentcart'),'Settings URL uses the shop parent');
$links=Admin::action_links(['deactivate'=>'Deactivate']);admin_check(str_contains($links['settings'],'admin.php?page=tools-for-fluentcart'),'Plugin action link uses shop settings');
$hook=get_plugin_page_hookname('tools-for-fluentcart','fluent-cart');Admin::assets($hook);
admin_check(wp_style_is('tffc-admin','enqueued')&&wp_script_is('tffc-admin','enqueued'),'Shop submenu loads local editor assets');
wp_dequeue_style('tffc-admin');wp_dequeue_script('tffc-admin');
$GLOBALS['menu']=[];$GLOBALS['submenu']=[];Admin::menu();
admin_check(in_array('tools-for-fluentcart',array_column($GLOBALS['submenu']['options-general.php']??[],2),true),'Missing shop menu registers the settings fallback');
admin_check(str_contains(Admin::settings_url(),'/wp-admin/options-general.php?page=tools-for-fluentcart'),'Fallback URL stays inside native admin');
$hook=get_plugin_page_hookname('tools-for-fluentcart','options-general.php');Admin::assets($hook);
admin_check(wp_style_is('tffc-admin','enqueued')&&wp_script_is('tffc-admin','enqueued'),'Fallback submenu loads local editor assets');
$GLOBALS['menu']=$old_menu;$GLOBALS['submenu']=$old_submenu;
echo 'TOTAL '.$GLOBALS['tffc_admin_checks']." checks\n";
