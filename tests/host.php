<?php
use Deckerweb\ToolsForFluentCart\{Settings,Admin,Rules};
$GLOBALS['tffc_host_checks']=0;
function host_check($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_host_checks'];echo "PASS $label\n";}
$old=get_option('fccr_settings',null);$current=get_option(Settings::OPTION,null);
update_option('fccr_settings',['enabled'=>true,'single'=>true]);delete_option(Settings::OPTION);
host_check(Settings::OPTION==='tffc_cart_rules','Module option uses the approved host prefix');
host_check(Settings::get()===Rules::defaults(),'Clean start ignores unpublished predecessor settings');
$links=Admin::action_links(['deactivate'=>'Deactivate']);host_check(array_key_first($links)==='settings','Settings action precedes deactivation');
host_check(str_contains($links['settings'],'page=tools-for-fluentcart'),'Settings action targets the renamed host');
wp_set_current_user(0);host_check(Admin::action_links(['deactivate'=>'Deactivate'])===['deactivate'=>'Deactivate'],'Settings link respects administrator permissions');wp_set_current_user(1);
$files=get_included_files();host_check((bool)array_filter($files,fn($f)=>str_ends_with($f,'modules/cart-rules/module.php')),'Cart Rules loads through its module entry point');
host_check((bool)array_filter($files,fn($f)=>str_contains($f,'deckerweb-plugin-library/src/Library.php')),'Shared Library loaded');
host_check(str_contains(file_get_contents(TFFC_DIR.'includes/deckerweb-plugin-library/version.php'),'0.7.0'),'Embedded Library version is 0.7.0');
if(null===$old){delete_option('fccr_settings');}else{update_option('fccr_settings',$old);}
if(null===$current){delete_option(Settings::OPTION);}else{update_option(Settings::OPTION,$current);}
echo 'TOTAL '.$GLOBALS['tffc_host_checks']." checks\n";
