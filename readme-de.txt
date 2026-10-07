=== Tools for FluentCart ===
Contributors: deckerweb
Tags: fluentcart, cart, order, quantity
Requires at least: 7.1.2
Tested up to: 7.1.2
Requires PHP: 8.2
Stable tag: 0.9.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Praktische Werkzeuge für deinen FluentCart-Shop. Das Modul Cart Rules bietet Ein-Artikel-Warenkörbe, Mindestmengen und Mindestwarenwert, Höchstmengen und Mengenschritte.

== Description ==

* Cart Rules ist das erste Modul der Tools-Serie.
* Fünf Presets mit anpassbaren Werten.
* Gesamtmengen und Mengen je Produkt im eigenen Modus kombinieren.
* Ein-Artikel-Modus ersetzt das bisherige Produkt und blendet die Mengenauswahl aus.
* Checkout-Prüfung anhand serverseitiger Warenkorbdaten.
* Einstellungen je Website, auch in Multisite.
* Englische Originaltexte, deutsche Du- und Sie-Übersetzungen.

Der eingebettete deckerweb Updater prüft öffentliche GitHub-Releases im WordPress-Updateablauf. GitHub erhält dabei die Netzwerkadresse der Installation und gewöhnliche HTTP-Metadaten; keine Zugangsdaten oder Kundenwarenkörbe. Der freiwillige gemeinsame Library-Onlinekatalog ist zunächst ausgeschaltet. Nach Aktivierung lädt er freigegebene öffentliche Metadaten von raw.githubusercontent.com. Gestaltung und Übersetzungen werden lokal ausgeliefert.

== Installation ==

Die installierbare ZIP-Datei unter Plugins → Plugin hinzufügen → Plugin hochladen installieren. FluentCart → Tools for FluentCart öffnen, ein Preset wählen, Werte prüfen, speichern und Regeln aktivieren. Beide älteren Ein-Artikel-Add-ons müssen deaktiviert sein. Bei einer Neuinstallation sind die Regeln ausgeschaltet. Ohne FluentCart bleibt Cart Rules pausiert und ein Hinweis erscheint im Admin. Die Tools-Einstellungen sind dann unter Einstellungen → Tools for FluentCart erreichbar. Sobald FluentCart installiert und aktiviert ist, stehen sie im Shop-Menü.

== Frequently Asked Questions ==

= Wann greifen die Regeln? =
Der Ein-Artikel-Modus passt bearbeitbare Warenkörbe automatisch an. Alle anderen Regeln werden beim Checkout geprüft. Hinweise erscheinen in den Standardansichten von Warenkorb und Checkout.

= Kann ich Presets kombinieren? =
Presets füllen die Felder als Ausgangspunkt aus. Im Modus für eigene Regeln lassen sich Grenzen kombinieren. Der Ein-Artikel-Modus ist eigenständig und setzt andere Grenzen beim Speichern zurück.

= Was zählt als Produkt? =
Jede Produktvariante zählt einzeln. Ein Bundle zählt als eine Warenkorbposition; seine Bestandteile werden nicht einzeln begrenzt.

= Begrenzt das Bestellungen pro Kunde? =
Nein. Die Regeln gelten für jeden Warenkorb, ohne Kundenhistorie oder Kontolimits.

= Funktioniert das in Multisite? =
Jede Website hat eigene Einstellungen. Netzwerkaktivierung erzeugt keine gemeinsame Bestellregel. Neue Websites starten mit ausgeschalteten Regeln.

= Welche Versionen und Integrationen werden unterstützt? =
Diese Entwicklungsversion ist für WordPress ab 7.1.2, PHP ab 8.2 und FluentCart 1.7.x vorgesehen. Bei anderen FluentCart-Minorversionen bleibt die Anbindung inaktiv. Abos, Zusatzangebote, Bundles, eigene Shopansichten und weitere Warenkorb-Add-ons vor dem Live-Einsatz testen. Tools kann ohne FluentCart aktiviert werden; das Modul bleibt bis zur Aktivierung einer unterstützten Version pausiert.

= Was passiert bei der Deinstallation? =
Einstellungen bleiben standardmäßig erhalten. Jede Website kann das Löschen ihrer Cart-Rules-Einstellungen erlauben. Temporäre Updater- und Library-Caches werden im jeweiligen Geltungsbereich bereinigt; Library-Daten erst beim letzten Host. FluentCart-Produkte, Bestellungen und Warenkörbe werden niemals gelöscht.

Vollständige Fragen nach Themen: docs/FAQ-de.md

== Changelog ==

= 0.9.0 · 2026-10-07 =
* Neu: Cart Rules mit Ein-Artikel-Warenkörben, Mindestmengen und Mindestbestellwert, Höchstmengen und Mengenschritten.

= 0.1.0–0.8.0 =
* Sonstiges: Entwicklungs- und Testversionen; nicht öffentlich veröffentlicht.

== License ==

Copyright © 2026 David Decker – DECKERWEB. GPL v2 oder höher; SPDX: GPL-2.0-or-later. Eingebettete deckerweb Plugin Library 0.7.0 und deckerweb Updater 2.1.0 stammen vom selben Autor und stehen unter GPL-2.0-or-later. Ihre stabilen Laufzeitquellen sind unverändert übernommen; Host-Anbindung und Sprachkataloge liegen getrennt vor. Dieses Plugin liefert weder FluentCart-Implementierung noch Premium-Code aus.
