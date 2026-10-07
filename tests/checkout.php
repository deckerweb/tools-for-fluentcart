<?php
use Deckerweb\ToolsForFluentCart\Settings;
use Deckerweb\ToolsForFluentCart\Rules;
use Deckerweb\ToolsForFluentCart\Integration;
use FluentCart\Api\Resource\FrontendResource\CartResource;
use FluentCart\Api\Cookie\Cookie;
use FluentCart\App\Models\Cart;
use Deckerweb\ToolsForFluentCart\Update;
$GLOBALS['tffc_tests']=0;
function check_checkout($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_tests'];echo "PASS $label\n";}
wp_set_current_user(1);
$cart=CartResource::get(['create'=>true]);
$cart->cart_data=[['id'=>999,'object_id'=>999,'post_id'=>0,'is_custom'=>true,'quantity'=>2,'unit_price'=>500,'subtotal'=>1000,'line_total'=>1000,'discount_total'=>0,'title'=>'Example','other_info'=>['payment_type'=>'one_time']]];$cart->save();
$s=Rules::defaults();$s['enabled']=true;$s['min_total']=6;update_option(Settings::OPTION,$s);
$r=Integration::checkout(true,['cart_hash'=>'untrusted-hash']);
check_checkout(is_wp_error($r) && str_contains($r->get_error_message(),'Add 4 more units'),'Checkout uses actual cart and rejects minimum violation');
$s['min_total']=0;$s['max_total']=1;update_option(Settings::OPTION,$s);check_checkout(is_wp_error(Integration::checkout(true,[])),'Checkout rejects maximum violation');
$s['max_total']=0;$s['step']=3;update_option(Settings::OPTION,$s);check_checkout(is_wp_error(Integration::checkout(true,[])),'Checkout rejects step violation');
$s['step']=1;$s['min_value']=1100;update_option(Settings::OPTION,$s);check_checkout(is_wp_error(Integration::checkout(true,[])),'Checkout rejects insufficient merchandise value');
$s['min_value']=1000;update_option(Settings::OPTION,$s);check_checkout(Integration::checkout(true,[])===true,'Checkout accepts boundary value');
$items=$cart->cart_data;$items[0]['quantity']=1;$items[0]['subtotal']=500;$cart->cart_data=$items;$cart->save();
$s['min_value']=0;$s['single']=true;update_option(Settings::OPTION,$s);check_checkout(Integration::checkout(true,[])===true,'Single-item checkout accepts valid cart');
$cart->cart_data=[];$cart->save();check_checkout(Integration::checkout(true,[])===true,'FluentCart keeps authority over empty-cart validation');
$other=['plugin'=>'other/other.php','type'=>'plugin','action'=>'update'];check_checkout(Update::validate('/tmp/other','/tmp',new stdClass(),$other)==='/tmp/other','Updater ignores unrelated plugin');
$context=['plugin'=>plugin_basename(TFFC_FILE),'type'=>'plugin','action'=>'update'];check_checkout(is_wp_error(Update::validate('/nonexistent','/tmp',new stdClass(),$context)),'Updater rejects invalid host package');
$previous=new WP_Error('previous','Previous');check_checkout(Update::validate($previous,'/tmp',new stdClass(),$context)===$previous,'Updater preserves previous validation error');
update_option(Settings::OPTION,Rules::defaults());$cart->delete();
echo 'TOTAL '.$GLOBALS['tffc_tests']." checks\n";
