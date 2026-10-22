from pathlib import Path
import json,shutil
root=Path(__file__).resolve().parents[1];src=json.loads((Path(__file__).resolve().parent/'docs-source.json').read_text());wiki=root/'wiki';wiki.mkdir(parents=True,exist_ok=True)
for lang,d in src.items():
 de=lang=='de'; suffix='-de' if de else '';other='README.md' if de else 'README-de.md'
 labels=['Kurzvorstellung','Auf einen Blick','Installation','Funktionen','FAQ','Änderungsverlauf','Projekt','Sicherheit und Unterstützung','Lizenz'] if de else ['About','At a Glance','Installation','Features','FAQ','Changelog','Project','Security and support','License']
 anchors=['about','glance','installation','features','faq','changelog','project','security','license']
 text='# Tools for FluentCart\n\n'+('[English]('+other+')' if de else '[Deutsch]('+other+')')+'\n\n'
 text+='![Tools for FluentCart](https://raw.githubusercontent.com/deckerweb/tools-for-fluentcart/main/assets-github/banner-github-'+lang+'.png?v=20261007-centered)\n\n'
 text+=f'<a id="about"></a>\n\n## {labels[0]}\n\n'+d['about']+'\n\n'
 text+='Version **0.9.0** · WordPress **7.1.2+** · PHP **8.2+** · FluentCart **1.7.x**\n\n'
 text+=('[Dokumentation](docs/DOCUMENTATION-de.md) · [FAQ](docs/FAQ-de.md)' if de else '[Documentation](docs/DOCUMENTATION.md) · [FAQ](docs/FAQ.md)')+'\n\n'
 text+=('**Inhalt**' if de else '**Contents**')+'\n\n'
 text+=' · '.join(f'[{label}](#{a})' for label,a in zip(labels,anchors))+'\n\n'
 blocks=[d['about'],'\n'.join('- '+s for s in d['glance']),d['install'],'\n\n'.join('### '+h+'\n\n'+s for h,s in d['features']),'\n\n'.join('### '+q+'\n\n'+a for q,a in d['faq'])+'\n\n'+('[Fragen nach Themen](docs/FAQ-de.md)' if de else '[FAQ by topic](docs/FAQ.md)'), '### 0.9.0 · 2026-10-07\n\n- **'+d['changelog'].split(': ',1)[0]+':** '+d['changelog'].split(': ',1)[1],d['author'],d['security']+'\n\n'+d['support'],d['license']]
 blocks[5]+='\n\n### 0.1.0–0.8.0\n\n- **'+d['development_history'].split(': ',1)[0]+':** '+d['development_history'].split(': ',1)[1]
 for label,a,b in zip(labels[1:],anchors[1:],blocks[1:]): text+=f'<a id="{a}"></a>\n\n## {label}\n\n{b}\n\n'
 (root/('README'+suffix+'.md')).write_text(text)
 txt='=== Tools for FluentCart ===\nContributors: deckerweb\nTags: fluentcart, cart, order, quantity\nRequires at least: 7.1.2\nTested up to: 7.1.2\nRequires PHP: 8.2\nStable tag: 0.9.0\nLicense: GPL v2 or later\nLicense URI: https://www.gnu.org/licenses/gpl-2.0.html\n\n'+d['about']+'\n\n== Description ==\n\n'+'\n'.join('* '+s for s in d['glance'])+'\n\n'+d['external']+'\n\n== Installation ==\n\n'+d['install']+'\n\n== Frequently Asked Questions ==\n\n'
 for q,a in d['faq']:txt+='= '+q+' =\n'+a+'\n\n'
 txt+=('Vollständige Fragen nach Themen: docs/FAQ-de.md\n' if de else 'Full FAQ by topic: docs/FAQ.md\n')+'\n== Changelog ==\n\n= 0.9.0 · 2026-10-07 =\n* '+d['changelog']+'\n\n== License ==\n\n'+d['license']+'\n'
 txt=txt.replace('\n\n== License ==','\n\n= 0.1.0–0.8.0 =\n* '+d['development_history']+'\n\n== License ==')
 (root/('readme'+suffix+'.txt')).write_text(txt)
 (root/'docs'/('changelog'+suffix+'.txt')).write_text('== Changelog ==\n\n= 0.9.0 · 2026-10-07 =\n* '+d['changelog']+'\n\n= 0.1.0–0.8.0 =\n* '+d['development_history']+'\n')
 (root/'docs'/('FAQ'+suffix+'.md')).write_text(('# Fragen nach Themen' if de else '# FAQ by topic')+'\n\n'+('[English](FAQ.md)' if de else '[Deutsch](FAQ-de.md)')+'\n\n'+('\n\n'.join('## '+q+'\n\n'+a for q,a in d['faq'])))
 (root/'docs'/('DATA'+suffix+'.md')).write_text(('# Daten und Datenschutz' if de else '# Data and privacy')+'\n\n'+('[English](DATA.md)' if de else '[Deutsch](DATA-de.md)')+'\n\n'+d['data']+'\n\n'+d['external'])
 (root/('SECURITY'+suffix+'.md')).write_text(('# Sicherheitsmeldungen' if de else '# Security reporting')+'\n\n'+('[English](SECURITY.md)' if de else '[Deutsch](SECURITY-de.md)')+'\n\n'+d['security']+'\n\n'+('Gemeinsame Komponenten: Library 1.1.0; Updater 2.2.1.' if de else 'Shared components: Library 1.1.0; Updater 2.2.1.'))
 doc=('# Dokumentation' if de else '# Documentation')+'\n\n'+d['install']+'\n\n'+ '\n\n'.join('## '+h+'\n\n'+t for h,t in d['features'])+'\n\n'+d['hooks']+'\n\n'+d['data']+'\n\n'+d['external']+'\n'
 (root/'docs'/('DOCUMENTATION'+suffix+'.md')).write_text(doc)
 doc += '\n## ' + ('Bekannte Einschränkung im Mutter-Plugin' if de else 'Known upstream limitation') + '\n\n' + d['multisite_note'] + '\n'
 (root/'docs'/('DOCUMENTATION'+suffix+'.md')).write_text(doc)
 (wiki/('Dokumentation.md' if de else 'Documentation.md')).write_text(doc)
 (wiki/('Fragen-nach-Themen.md' if de else 'FAQ-by-topic.md')).write_text((root/'docs'/('FAQ'+suffix+'.md')).read_text().replace('(FAQ.md)','(FAQ-by-topic)').replace('(FAQ-de.md)','(Fragen-nach-Themen)'))
 (wiki/('Aenderungsverlauf.md' if de else 'Changelog.md')).write_text('# '+labels[5]+'\n\n### 0.9.0 · 2026-10-07\n\n- '+d['changelog']+'\n\n### 0.1.0–0.8.0\n\n- **'+d['development_history'].split(': ',1)[0]+':** '+d['development_history'].split(': ',1)[1])
(wiki/'Home.md').write_text('# Tools for FluentCart\n\n![Tools for FluentCart](https://raw.githubusercontent.com/deckerweb/tools-for-fluentcart/main/assets-github/banner-github-en.png)\n\nFine-tune your store. / Dein Shop. Fein abgestimmt.\n\n## English\n\n- [Documentation](Documentation)\n- [FAQ by topic](FAQ-by-topic)\n- [Changelog](Changelog)\n\n## Deutsch\n\n- [Dokumentation](Dokumentation)\n- [Fragen nach Themen](Fragen-nach-Themen)\n- [Änderungsverlauf](Aenderungsverlauf)\n')
(wiki/'_Sidebar.md').write_text('[Home](Home)\n\n**English**\n\n- [Documentation](Documentation)\n- [FAQ by topic](FAQ-by-topic)\n- [Changelog](Changelog)\n\n**Deutsch**\n\n- [Dokumentation](Dokumentation)\n- [Fragen nach Themen](Fragen-nach-Themen)\n- [Änderungsverlauf](Aenderungsverlauf)\n')
print('Generated synchronized EN/DE readmes, FAQ, documentation, data, changelogs and security guidance')

# Generate the localized administrative history from the same public content source.
entry=src['en']['changelog'].split(': ',1)[1].replace("'", "\\'")
history="<?php\n/** Generated public release history. @package ToolsForFluentCart */\ndefined( 'ABSPATH' ) || exit;\nreturn [ [ 'version' => '0.9.0', 'date' => '2026-10-07', 'changes' => [ 'New' => [ __( '"+entry+"', 'tools-for-fluentcart' ) ] ] ] ];\n"
historical=src['en']['development_history'].split(': ',1)[1].replace("'", "\\'")
history=history.replace(" ] ];\n", " ], [ 'version' => '0.1.0–0.8.0', 'date' => '', 'changes' => [ 'Misc' => [ __( '"+historical+"', 'tools-for-fluentcart' ) ] ] ] ];\n")
(root/'includes/history.php').write_text(history)

# Supply matching Wiki readmes and a compact grouped navigation.
for lang in ['en','de']:
 suffix='-de' if lang=='de' else ''
 text=(root/('README'+suffix+'.md')).read_text()
 text=text.replace('(README.md)','(README)').replace('(README-de.md)','(README-de)')
 text=text.replace('(docs/DOCUMENTATION.md)','(Documentation)').replace('(docs/DOCUMENTATION-de.md)','(Dokumentation)').replace('(docs/FAQ.md)','(FAQ-by-topic)').replace('(docs/FAQ-de.md)','(Fragen-nach-Themen)')
 (wiki/('README'+suffix+'.md')).write_text(text)

# Site and Wiki entry points share the same descriptions and feature lists.
base='https://github.com/deckerweb/tools-for-fluentcart'
page='---\nlayout: default\n---\n\n# Tools for FluentCart\n\n'
for lang,d in src.items():
 de=lang=='de';slogan='Dein Shop. Fein abgestimmt.' if de else 'Fine-tune your store.'
 banner=f'https://raw.githubusercontent.com/deckerweb/tools-for-fluentcart/main/assets-github/banner-github-{lang}.png?v=20261007-centered'
 welcome='# Tools for FluentCart\n\n![Tools for FluentCart]('+banner+')\n\n**'+slogan+'**\n\n'+d['about']+'\n\n'+'\n'.join('- '+x for x in d['glance'])+'\n\n'
 welcome+=('[English](Home)\n\n[Readme](README-de) · [Dokumentation](Dokumentation) · [Fragen nach Themen](Fragen-nach-Themen) · [Änderungsverlauf](Aenderungsverlauf)' if de else '[Deutsch](Home-de)\n\n[Readme](README) · [Documentation](Documentation) · [FAQ by topic](FAQ-by-topic) · [Changelog](Changelog)')+'\n'
 (wiki/('Home-de.md' if de else 'Home.md')).write_text(welcome)
 page+=('## Deutsch' if de else '## English')+'\n\n![Tools for FluentCart](assets-github/banner-github-'+lang+'.png?v=20261007-centered)\n\n**'+slogan+'**\n\n'+d['about']+'\n\n'
 page+=('Version 0.9.1 ist veröffentlicht.' if de else 'Version 0.9.1 is available.')+'\n\n[Repository]('+base+') · ['+('Dokumentation' if de else 'Documentation')+']('+base+'/wiki/'+('Dokumentation' if de else 'Documentation')+') · [Download 0.9.1]('+base+'/releases/download/v0.9.1/tools-for-fluentcart-0.9.1.zip)\n\n'
 page+='\n'+d['multisite_note']+'\n\n'
 # Group full FAQ by use case without changing the seven readme questions.
 full=('# Fragen nach Themen' if de else '# FAQ by topic')+'\n\n'+('[English](FAQ.md)' if de else '[Deutsch](FAQ-de.md)')+'\n\n'
 for heading,indices in [('Einrichtung und Alltag' if de else 'Setup and everyday use',[0,1]),('Produkte und Grenzen' if de else 'Products and limits',[2,3]),('Verwaltung und Kompatibilität' if de else 'Administration and compatibility',[4,5,6])]:
  full+='## '+heading+'\n\n'
  for i in indices:
   q,a= d['faq'][i];full+='### '+q+'\n\n'+a+'\n\n'
 (root/'docs'/('FAQ-de.md' if de else 'FAQ.md')).write_text(full)
 (wiki/('Fragen-nach-Themen.md' if de else 'FAQ-by-topic.md')).write_text(full.replace('(FAQ.md)','(FAQ-by-topic)').replace('(FAQ-de.md)','(Fragen-nach-Themen)'))
page+='## Support · Unterstützung\n\n[Ko-fi](https://ko-fi.com/deckerweb) · [Buy Me a Coffee](https://buymeacoffee.com/daveshine) · [PayPal](https://paypal.me/deckerweb)\n'
(root/'index.md').write_text(page)
(wiki/'_Sidebar.md').write_text('**English**\n\n[Home](Home) · [Readme](README)\n\n- [Documentation](Documentation)\n- [FAQ by topic](FAQ-by-topic)\n- [Changelog](Changelog)\n\n**Deutsch**\n\n[Startseite](Home-de) · [Readme](README-de)\n\n- [Dokumentation](Dokumentation)\n- [Fragen nach Themen](Fragen-nach-Themen)\n- [Änderungsverlauf](Aenderungsverlauf)\n')

# Maintenance overlay: preserve the public 0.9.0 and development history.
maintenance = {
 'en': 'Updated shared Library and updater integration; protected paused rule settings and improved admin notices, save confirmations and changelog dialog reliability.',
 'de': 'Gemeinsame Library und Update-Anbindung aktualisiert; pausierte Regelfelder geschützt sowie Admin-Hinweise, Speicherbestätigungen und Änderungsverlauf-Dialog verbessert.'
}
for lang in ['en', 'de']:
 suffix = '-de' if lang == 'de' else ''
 prefix = 'Verbessert' if lang == 'de' else 'Improved'
 paths = [root / ('README' + suffix + '.md'), root / ('readme' + suffix + '.txt'), root / 'docs' / ('changelog' + suffix + '.txt'), wiki / ('Aenderungsverlauf.md' if lang == 'de' else 'Changelog.md'), wiki / ('README' + suffix + '.md')]
 for path in paths:
  text = path.read_text().replace('Version **0.9.0**', 'Version **0.9.1**').replace('Stable tag: 0.9.0', 'Stable tag: 0.9.1').replace('Tested up to: 7.1.2', 'Tested up to: 7.1')
  text = text.replace('### 0.9.0 · 2026-10-07', '### 0.9.1 · 2026-10-22\n\n- **' + prefix + ':** ' + maintenance[lang] + '\n\n### 0.9.0 · 2026-10-07')
  text = text.replace('= 0.9.0 · 2026-10-07 =', '= 0.9.1 · 2026-10-22 =\n* ' + prefix + ': ' + maintenance[lang] + '\n\n= 0.9.0 · 2026-10-07 =')
  if path.suffix == '.txt' and path.name.startswith('readme'):
   text = text.replace(src[lang]['about'], ('Warenkorb-Regeln für FluentCart: einzelne Artikel, Mindestmengen und Bestellwerte, Mengenlimits und Mengenschritte.' if lang == 'de' else 'Cart Rules for FluentCart: single-item carts, minimum quantities and order values, quantity limits and steps.'), 1)
  path.write_text(text)
 release = '# Tools for FluentCart 0.9.1\n\n- **' + prefix + ':** ' + maintenance[lang] + '\n\n' + src[lang]['install'] + '\n'
 release += '\n**' + ('Bekannte Einschränkung im Mutter-Plugin' if lang == 'de' else 'Known upstream limitation') + ':** ' + src[lang]['multisite_note'] + '\n'
 (root / ('RELEASE-de.md' if lang == 'de' else 'RELEASE-en.md')).write_text(release)
# Real release/download links on Pages retain the latest published version until publication.
entry = maintenance['en'].replace("'", "\\'")
history_path = root / 'includes/history.php'
history_path.write_text(history_path.read_text().replace("return [", "return [ [ 'version' => '0.9.1', 'date' => '2026-10-22', 'changes' => [ 'Improved' => [ __( '" + entry + "', 'tools-for-fluentcart' ) ] ] ],", 1))

def template(name, values):
 text = (Path(__file__).parent / 'wiki-templates' / name).read_text()
 import re
 text = re.sub(r'<!--.*?-->\s*', '', text, flags=re.S)
 for key, value in values.items(): text = text.replace('{{' + key + '}}', value)
 assert '{{' not in text
 return text
base = 'https://github.com/deckerweb/tools-for-fluentcart'
sidebar = template('_Sidebar.md', {
 'WIKI_HOME_EN': 'Home', 'GETTING_STARTED_EN': 'README#installation', 'USER_GUIDE_EN': 'Documentation', 'FAQ_EN': 'FAQ-by-topic', 'CHANGELOG_EN': 'Changelog', 'HELP_SECURITY_EN': base + '/blob/main/SECURITY.md',
 'WIKI_HOME_DE': 'Home-de', 'GETTING_STARTED_DE': 'README-de#installation', 'USER_GUIDE_DE': 'Dokumentation', 'FAQ_DE': 'Fragen-nach-Themen', 'CHANGELOG_DE': 'Aenderungsverlauf', 'HELP_SECURITY_DE': base + '/blob/main/SECURITY-de.md'
}).replace('[Changelog](Aenderungsverlauf)', '[Änderungsverlauf](Aenderungsverlauf)')
(wiki / '_Sidebar.md').write_text(sidebar)
(wiki / '_Footer.md').write_text(template('_Footer.md', {'PLUGIN_NAME': 'Tools for FluentCart', 'WIKI_HOME': base + '/wiki', 'REPOSITORY': base, 'RELEASES': base + '/releases', 'ISSUES': base + '/issues', 'DISCUSSIONS': base + '/discussions', 'SECURITY_POLICY': base + '/security/policy', 'COPYRIGHT_YEARS': '2026'}))
