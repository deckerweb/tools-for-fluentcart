from pathlib import Path
import json,re,struct
root=Path(__file__).resolve().parents[1]; out=root/'languages'
source={}
for p in root.rglob('*.php'):
 if 'tests' in p.relative_to(root).parts or 'deckerweb-plugin-library' in str(p) or p.name=='deckerweb-github-release-updater-v2.php':continue
 for s in re.findall(r"(?:__|esc_html__|esc_attr__)\(\s*'((?:\\'|[^'])*)'\s*,\s*'tools-for-fluentcart'",p.read_text()): source.setdefault(s,[]).append(str(p.relative_to(root)))
dictionary=json.loads((Path(__file__).resolve().parent/'translations.json').read_text())
assert set(source)==set(dictionary),(set(source)-set(dictionary),set(dictionary)-set(source))
formal={
'Cart Rules is paused. Install and activate FluentCart 1.7.x to use this module. Tools for FluentCart remains available in the admin.':'Warenkorb-Regeln sind pausiert. Installieren und aktivieren Sie FluentCart 1.7.x, um das Modul zu verwenden. Tools for FluentCart bleibt im Admin verfügbar.',

'Deactivate the earlier FluentCart Cart Rules and single-item add-ons before using Tools for FluentCart.':'Deaktivieren Sie das bisherige FluentCart Cart Rules und die älteren Ein-Artikel-Add-ons, bevor Sie Tools for FluentCart verwenden.',
'Order rules for your FluentCart shop.':'Bestellregeln für Ihren FluentCart-Shop.',
'Please check the quantities in your cart.':'Bitte prüfen Sie die Mengen in Ihrem Warenkorb.',
'Please refresh your cart and try again.':'Bitte laden Sie Ihren Warenkorb neu und versuchen Sie es erneut.',
'Your cart may contain no more than %s units.':'Ihr Warenkorb darf höchstens %s Stück enthalten.',
'Fine-tune your store.':'Ihr Shop. Fein abgestimmt.',
'Product variations count separately. A bundle counts as one cart row. Locked payment carts are preserved and checked at checkout. Test subscriptions, bundles and order bumps with your chosen rules before live use.':'Produktvarianten zählen einzeln. Ein Bundle zählt als eine Warenkorbposition. Gesperrte Zahlungswarenkörbe bleiben erhalten und werden beim Checkout geprüft. Testen Sie Abos, Bundles und Zusatzangebote mit den gewählten Regeln vor dem Live-Einsatz.',
'Rules start disabled. Configure them here, then enable them for this website. Other websites keep their own settings.':'Die Regeln sind zunächst ausgeschaltet. Richten Sie sie hier ein und aktivieren Sie sie anschließend für diese Website. Andere Websites behalten ihre eigenen Einstellungen.',
'Selecting a preset fills the rule fields. Review the values before saving. You can combine rules in custom mode.':'Ein Preset füllt die Regelfelder aus. Prüfen Sie die Werte vor dem Speichern. Im Modus „Eigene Regeln“ können Sie Regeln kombinieren.',
'The private update could not be authorized. Check the repository credentials and refresh updates.':'Das private Update konnte nicht autorisiert werden. Bitte prüfen Sie die Repository-Zugangsdaten und suchen Sie erneut nach Updates.',
'The private update download failed. Check credentials and try again.':'Der Download des privaten Updates ist fehlgeschlagen. Bitte prüfen Sie die Zugangsdaten und versuchen Sie es erneut.'
}
def quote(s):return json.dumps(s,ensure_ascii=False)
def makepo(lang,values):
 header=f'Project-Id-Version: Tools for FluentCart 0.9.0\nLanguage: {lang}\nContent-Type: text/plain; charset=UTF-8\nPlural-Forms: nplurals=2; plural=(n != 1);\n'
 text='msgid ""\nmsgstr '+quote(header)+'\n\n'
 for s in sorted(source):
  text+='#: '+ ' '.join(sorted(set(source[s])))+'\n'
  if '%' in s:text+='#, php-format\n'
  text+='msgid '+quote(s)+'\nmsgstr '+quote(values.get(s,''))+'\n\n'
 return text,header
text,_=makepo('',{});(out/'tools-for-fluentcart.pot').write_text(text)
for lang in ['de_DE','de_DE_formal']:
 values=dict(dictionary)
 if lang.endswith('formal'):values.update(formal)
 for s,v in values.items():assert sorted(re.findall(r'%\d*\$?s',s))==sorted(re.findall(r'%\d*\$?s',v)),s
 text,header=makepo(lang,values);(out/f'tools-for-fluentcart-{lang}.po').write_text(text)
 values['']=header
 ids=sorted(values);keys=b'';vals=b'';io=[];vo=[]
 for s in ids:
  k=s.encode();v=values[s].encode();io.append((len(k),len(keys)));vo.append((len(v),len(vals)));keys+=k+b'\0';vals+=v+b'\0'
 n=len(ids);base=28+16*n;vbase=base+len(keys)
 binary=struct.pack('<7I',0x950412de,0,n,28,28+8*n,0,0)
 binary+=b''.join(struct.pack('<2I',l,base+o) for l,o in io)+b''.join(struct.pack('<2I',l,vbase+o) for l,o in vo)+keys+vals
 (out/f'tools-for-fluentcart-{lang}.mo').write_bytes(binary)
print(f'{len(source)} messages translated for German informal and formal; placeholders verified')
