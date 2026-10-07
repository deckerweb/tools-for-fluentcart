<?php
use Deckerweb\ToolsForFluentCart\{Rules,Settings,Integration};
use FluentCart\App\Models\{ProductDetail,ProductVariation};
use FluentCart\Api\Resource\FrontendResource\CartResource;
use FluentCart\App\App;
$GLOBALS['tffc_product_tests']=0;
function product_check($ok,$label){if(!$ok){throw new Exception($label);}++$GLOBALS['tffc_product_tests'];echo "PASS $label\n";}
wp_set_current_user(1);
$s=Rules::defaults();$s['enabled']=true;$s['single']=true;update_option(Settings::OPTION,$s);
$posts=[];$variations=[];
for($i=0;$i<2;$i++){
 $pid=wp_insert_post(['post_type'=>'fluent-products','post_status'=>'publish','post_title'=>'Cart Rules fixture '.$i]);$posts[]=$pid;
 $d=ProductDetail::create(['post_id'=>$pid,'fulfillment_type'=>'physical','min_price'=>500,'max_price'=>500,'variation_type'=>'simple','stock_availability'=>'in-stock','manage_stock'=>1,'other_info'=>[]]);
 $v=ProductVariation::create(['post_id'=>$pid,'variation_title'=>'Default','item_price'=>500,'fulfillment_type'=>'physical','payment_type'=>'onetime','item_status'=>'active','stock_status'=>'in-stock','manage_stock'=>1,'available'=>1,'total_stock'=>1,'other_info'=>[]]);
 $d->default_variation_id=$v->id;$d->save();$variations[]=$v;
}
$c=CartResource::get(['create'=>true]);$c->cart_data=[];$c->save();
function request_add($id,$q){$_POST=['item_id'=>$id,'quantity'=>$q,'by_input'=>'0'];$_REQUEST=$_POST;App::request()->merge($_POST);Integration::prepare_ajax();return CartResource::update(App::request()->all());}
$r=request_add($variations[0]->id,99);$c=$c->fresh();product_check(!is_wp_error($r) && count($c->cart_data)===1 && $c->cart_data[0]['quantity']==1,'Real product AJAX input clamps 99 to one before stock checks');
$r=request_add($variations[0]->id,1);$c=$c->fresh();product_check(!is_wp_error($r) && $c->cart_data[0]['quantity']==1,'Repeated addition succeeds with stock of one');
$r=request_add($variations[1]->id,1);$c=$c->fresh();product_check(!is_wp_error($r) && count($c->cart_data)===1 && $c->cart_data[0]['object_id']==$variations[1]->id,'Real second product replaces first');
$deny=static function(){return new WP_Error('test_denied','Purchase denied');};add_filter('fluent_cart/cart/can_purchase',$deny,200);
$r=request_add($variations[0]->id,1);product_check(is_wp_error($r) && $r->get_error_code()==='test_denied','Independent product purchase restriction remains effective');remove_filter('fluent_cart/cart/can_purchase',$deny,200);
$r=request_add($variations[1]->id,0);$c=$c->fresh();product_check(!is_wp_error($r) && $c->cart_data===[],'Real zero-quantity removal succeeds');
$r=request_add(2147483647,1);product_check(is_wp_error($r),'Missing product rejected by FluentCart');
$c->delete();foreach($variations as $v){$v->delete();}foreach($posts as $pid){ProductDetail::where('post_id',$pid)->delete();wp_delete_post($pid,true);}update_option(Settings::OPTION,Rules::defaults());
echo 'TOTAL '.$GLOBALS['tffc_product_tests']." checks\n";
