<?php
use Deckerweb\ToolsForFluentCart\{Settings,Rules};
require_once ABSPATH.'wp-admin/includes/plugin.php';
$GLOBALS['tffc_ms_tests']=0;
function ms_check($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_ms_tests'];echo "PASS $label\n";}
ms_check(is_multisite() && is_plugin_active_for_network('tools-for-fluentcart/tools-for-fluentcart.php'),'Plugin network activated');
$original=get_current_blog_id();$s=Rules::defaults();$s['enabled']=true;$s['min_total']=6;$s['delete_data']=true;update_option(Settings::OPTION,$s);
$id=wpmu_create_blog('127.0.0.1:8894','/tffc-fixture/','Fixture',1);ms_check(!is_wp_error($id),'New multisite site created');
switch_to_blog($id);ms_check(!Settings::get()['enabled'],'New site starts with disabled rules');$s=Rules::defaults();$s['enabled']=true;$s['max_total']=5;update_option(Settings::OPTION,$s);restore_current_blog();
ms_check(Settings::get()['min_total']===6 && Settings::get()['max_total']===0,'Site settings remain isolated');
// An independent installed host must preserve shared component settings.
$fixture=WP_PLUGIN_DIR.'/tffc-library-fixture';wp_mkdir_p($fixture.'/includes/deckerweb-plugin-library');file_put_contents($fixture.'/fixture.php',"<?php\n/** Plugin Name: Library fixture */\n");file_put_contents($fixture.'/includes/deckerweb-plugin-library/bootstrap.php','<?php');wp_clean_plugins_cache(false);
update_network_option(get_current_network_id(),'deckerweb_library_settings_v1',['delete_settings'=>true,'catalog_enabled'=>false]);
$before=FluentCart\App\Models\ProductVariation::count();
define('WP_UNINSTALL_PLUGIN','tools-for-fluentcart/tools-for-fluentcart.php');require TFFC_DIR.'uninstall.php';
ms_check(get_current_blog_id()===$original,'Uninstall restores original site context');
ms_check(get_option(Settings::OPTION,false)===false,'Uninstall deletes opted-in host settings');
switch_to_blog($id);ms_check(get_option(Settings::OPTION,false)!==false,'Uninstall preserves non-opted-in site settings');restore_current_blog();
ms_check(get_network_option(get_current_network_id(),'deckerweb_library_settings_v1',false)!==false,'Shared settings preserved while another host remains installed');
ms_check(FluentCart\App\Models\ProductVariation::count()===$before,'Uninstall leaves FluentCart products intact');
unlink($fixture.'/includes/deckerweb-plugin-library/bootstrap.php');rmdir($fixture.'/includes/deckerweb-plugin-library');rmdir($fixture.'/includes');unlink($fixture.'/fixture.php');rmdir($fixture);wp_clean_plugins_cache(false);
require_once ABSPATH.'wp-admin/includes/ms.php';wpmu_delete_blog($id,true);update_option(Settings::OPTION,Rules::defaults());delete_network_option(get_current_network_id(),'deckerweb_library_settings_v1');
echo 'TOTAL '.$GLOBALS['tffc_ms_tests']." checks\n";
