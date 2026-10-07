<?php
use Deckerweb\ToolsForFluentCart\Update;
require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';
$GLOBALS['tffc_update_tests']=0;
function update_check($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_update_tests'];echo "PASS $label\n";}
$base=get_temp_dir().'tffc-update-fixture-'.wp_generate_uuid4();$source=$base.'/tools-for-fluentcart';wp_mkdir_p($source);
$file=$source.'/tools-for-fluentcart.php';$original=file_get_contents(TFFC_FILE);file_put_contents($file,str_replace('0.9.0','0.9.1',$original));
$key=plugin_basename(TFFC_FILE);$previous=get_site_transient('update_plugins');set_site_transient('update_plugins',(object)['response'=>[$key=>(object)['new_version'=>'0.9.1']]]);
$ctx=['plugin'=>$key,'type'=>'plugin','action'=>'update'];
update_check(Update::validate($source,$base,new stdClass(),$ctx)===$source,'Updater accepts offered package with matching headers');
$bulk=new Plugin_Upgrader();$bulk->bulk=true;update_check(Update::validate($source,$base,$bulk,['plugin'=>$key])===$source,'Bulk update validates the matching host');
file_put_contents($file,str_replace('0.9.0','0.9.2',$original));update_check(is_wp_error(Update::validate($source,$base,new stdClass(),$ctx)),'Updater rejects version mismatch');
file_put_contents($file,str_replace(['0.9.0','Requires PHP: 8.2'],['0.9.1','Requires PHP: 99.0'],$original));update_check(is_wp_error(Update::validate($source,$base,new stdClass(),$ctx)),'Updater rejects incompatible PHP requirement');
file_put_contents($file,str_replace(['0.9.0','Text Domain: tools-for-fluentcart'],['0.9.1','Text Domain: other-plugin'],$original));update_check(is_wp_error(Update::validate($source,$base,new stdClass(),$ctx)),'Updater rejects wrong identity');
set_site_transient('update_plugins',$previous);unlink($file);rmdir($source);rmdir($base);
echo 'TOTAL '.$GLOBALS['tffc_update_tests']." checks\n";
