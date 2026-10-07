<?php
$old=WP_PLUGIN_DIR.'/fluentcart-cart-rules';
require_once $old.'/includes/deckerweb-plugin-library/bootstrap.php';
deckerweb_library_register_v2($old.'/fluentcart-cart-rules.php',[],$old.'/includes/deckerweb-plugin-library');
require_once WP_PLUGIN_DIR.'/tools-for-fluentcart/tools-for-fluentcart.php';
do_action('plugins_loaded');
if(!class_exists('Deckerweb\\PluginLibrary\\V0_7_0\\Library',false)){throw new Exception('New compatible Library was not selected');}
if(class_exists('Deckerweb\\PluginLibrary\\V0_6_0\\Library',false)){throw new Exception('Older implementation unexpectedly loaded');}
echo "TOTAL 2 checks: 0.7.0 wins over registered 0.6.0; older runtime stays unloaded\n";
