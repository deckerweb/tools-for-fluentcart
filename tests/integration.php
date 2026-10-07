<?php
use Deckerweb\ToolsForFluentCart\Rules;
use Deckerweb\ToolsForFluentCart\Settings;
use Deckerweb\ToolsForFluentCart\Integration;
use FluentCart\App\Models\Cart;
use FluentCart\App\Models\ProductVariation;
use FluentCart\Api\Resource\FrontendResource\CartResource;

$GLOBALS['tffc_tests'] = 0;
function verify($ok,$message) {  if (!$ok) { throw new Exception($message); } ++$GLOBALS['tffc_tests']; echo "PASS $message\n"; }
function row($id,$q=1,$unit=500,$discount=0) { return ['id'=>$id,'object_id'=>$id,'post_id'=>0,'is_custom'=>true,'quantity'=>$q,'unit_price'=>$unit,'price'=>$unit,'subtotal'=>$q*$unit,'line_total'=>$q*$unit-$discount,'discount_total'=>$discount,'post_title'=>'Example','title'=>'Example','other_info'=>['payment_type'=>'one_time']]; }
wp_set_current_user(1);
delete_option(Settings::OPTION);
verify(Integration::available(),'Adapter active on actual FluentCart 1.7.0');
verify(!Settings::get()['enabled'],'Fresh install leaves rules disabled');
$s=Rules::defaults();$s['enabled']=true;
foreach ([['min_total',6,[row(1,5)],true],['min_total',6,[row(1,3),row(2,3)],false],['min_item',3,[row(1,2),row(2,4)],true],['max_total',5,[row(1,3),row(2,3)],true],['max_item',2,[row(1,3)],true],['step',6,[row(1,6),row(2,12)],false],['step',6,[row(1,7)],true],['min_value',1000,[row(1,2,500,100)],true],['min_value',1000,[row(1,2)],false]] as $case) {
 $a=$s;$a[$case[0]]=$case[1]; verify((bool)Rules::violations($case[2],$a)===$case[3],$case[0].' boundary '.$GLOBALS['tffc_tests']);
}
$a=$s;$a['min_total']=7;$a['max_total']=10;$a['step']=6;verify((bool)Rules::conflicts($a),'Impossible total-step configuration rejected');
$a=$s;$a['min_item']=5;$a['max_item']=4;verify((bool)Rules::conflicts($a),'Minimum greater than maximum rejected');
$valid=Settings::sanitize(['enabled'=>'1','min_value'=>'30,25','step'=>'1']);verify($valid['min_value']===3025,'Decimal comma converted exactly');
verify(Settings::sanitize($valid)===$valid,'Repeated sanitization preserves monetary units');
$valid=Settings::sanitize(['enabled'=>'1','min_value'=>'30.2','step'=>'1']);verify($valid['min_value']===3020,'Decimal dot converted exactly');
$invalid=Settings::sanitize(['enabled'=>'1','min_total'=>'-1']);verify($invalid===Settings::get(),'Invalid negative input preserves settings');
$invalid=Settings::sanitize(['enabled'=>'1','min_total'=>['bad']]);verify($invalid===Settings::get(),'Array input rejected');
wp_set_current_user(0);verify(Settings::sanitize(['enabled'=>'1'])===Settings::get(),'Unauthorized settings edit rejected');wp_set_current_user(1);
$s['single']=true;update_option(Settings::OPTION,$s);
$c=Cart::query()->create(['cart_hash'=>'tffc-test-'.wp_generate_uuid4(),'cart_data'=>[row(1),row(2,7)],'stage'=>'draft','cart_group'=>'global']);
verify(count($c->cart_data)===1 && (int)$c->cart_data[0]['quantity']===1 && $c->cart_data[0]['object_id']===2,'Real model persistence replaces old product and clamps quantity');
verify($c->cart_data[0]['subtotal']===500,'Single-item amounts match one unit');
$c->addItem(row(3,8));verify(count($c->cart_data)===1 && $c->cart_data[0]['object_id']===3,'Native addItem replaces current row');
$c->addItem(row(3,2),0);verify((int)$c->cart_data[0]['quantity']===1,'Repeated native add stays at one');
$c->removeItem(3);verify($c->cart_data===[],'Native removal keeps empty cart');
$s['single']=false;update_option(Settings::OPTION,$s);
$c->cart_data=[row(1),row(2,2)];$c->save();
$s['single']=true;update_option(Settings::OPTION,$s);
$c=$c->fresh();verify(count($c->cart_data)===1 && (int)$c->cart_data[0]['quantity']===1,'Existing carts repaired on retrieval');
$c->checkout_data=['is_locked'=>'yes'];$c->order_id=9999;$c->cart_data=[row(1,2),row(2)];$c->save();
verify(count($c->cart_data)===2,'Locked carts are never rewritten');
$locked=$c->fresh();verify(count($locked->cart_data)===2,'Locked carts preserved on read');
$errors=Rules::violations($locked->cart_data,$s);verify($errors[0]['code']==='single','Invalid locked contents fail validation');
$previous=new WP_Error('previous','Previous validation');verify(Integration::checkout($previous,[])===$previous,'Other checkout validation remains intact');
$_POST=['quantity'=>'99'];$_REQUEST=$_POST;Integration::prepare_ajax();verify($_POST['quantity']==='1' && $_POST['by_input']==='1','Single-item AJAX positive quantity normalized');
$_POST=['quantity'=>'0'];$_REQUEST=$_POST;Integration::prepare_ajax();verify($_POST['quantity']==='0','Zero-quantity removal preserved');
$_POST=['quantity'=>'-1'];$_REQUEST=$_POST;Integration::prepare_ajax();verify($_POST['quantity']==='-1','Negative decrement preserved');
$s['single']=false;$s['min_total']=6;update_option(Settings::OPTION,$s);
ob_start();Integration::hints(['cart'=>(object)['cart_data'=>[row(1,4)]]]);$html=ob_get_clean();verify(str_contains($html,'Add 2 more units'),'Checkout guidance reports remaining quantity');
ob_start();Integration::line_hint([]);$html=ob_get_clean();verify(str_contains($html,'Minimum cart quantity: 6'),'Dynamic cart line includes rule guidance');
$s=Rules::defaults();update_option(Settings::OPTION,$s);
verify(Rules::violations([row(1,99)],$s)===[],'Disabled rules have no effect');
$de=TFFC_DIR.'languages/tools-for-fluentcart-de_DE.mo';load_textdomain('tools-for-fluentcart',$de);
verify(__('Fine-tune your store.','tools-for-fluentcart')==='Dein Shop. Fein abgestimmt.','German informal catalog works');
unload_textdomain('tools-for-fluentcart');load_textdomain('tools-for-fluentcart',TFFC_DIR.'languages/tools-for-fluentcart-de_DE_formal.mo');
verify(__('Fine-tune your store.','tools-for-fluentcart')==='Ihr Shop. Fein abgestimmt.','German formal catalog works');
Cart::query()->where('cart_hash',$c->cart_hash)->delete();
echo 'TOTAL '.$GLOBALS['tffc_tests']." checks\n";
