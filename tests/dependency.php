<?php
if(defined('FLUENTCART_VERSION')){throw new Exception('FluentCart should be skipped for this fixture');}
if(\Deckerweb\ToolsForFluentCart\Integration::available()){throw new Exception('Adapter should remain inactive');}
ob_start();\Deckerweb\ToolsForFluentCart\Admin::render();$html=ob_get_clean();
if(!str_contains($html,'Tools for FluentCart') || !str_contains($html,'tffc_cart_rules')){throw new Exception('Settings should remain accessible');}
echo "TOTAL 1 checks: missing dependency is inert and settings remain available\n";
