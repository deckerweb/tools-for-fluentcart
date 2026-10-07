# Tools for FluentCart

[English](README)

![Tools for FluentCart](https://raw.githubusercontent.com/deckerweb/tools-for-fluentcart/main/assets-github/banner-github-de.png?v=20261007-centered)

<a id="about"></a>

## Kurzvorstellung

Praktische Werkzeuge für deinen FluentCart-Shop. Das Modul Cart Rules bietet Ein-Artikel-Warenkörbe, Mindestmengen und Mindestwarenwert, Höchstmengen und Mengenschritte.

Version **0.9.0** · WordPress **7.1.2+** · PHP **8.2+** · FluentCart **1.7.x**

[Dokumentation](Dokumentation) · [FAQ](Fragen-nach-Themen)

**Inhalt**

[Kurzvorstellung](#about) · [Auf einen Blick](#glance) · [Installation](#installation) · [Funktionen](#features) · [FAQ](#faq) · [Änderungsverlauf](#changelog) · [Projekt](#project) · [Sicherheit und Unterstützung](#security) · [Lizenz](#license)

<a id="glance"></a>

## Auf einen Blick

- Cart Rules ist das erste Modul der Tools-Serie.
- Fünf Presets mit anpassbaren Werten.
- Gesamtmengen und Mengen je Produkt im eigenen Modus kombinieren.
- Ein-Artikel-Modus ersetzt das bisherige Produkt und blendet die Mengenauswahl aus.
- Checkout-Prüfung anhand serverseitiger Warenkorbdaten.
- Einstellungen je Website, auch in Multisite.
- Englische Originaltexte, deutsche Du- und Sie-Übersetzungen.

<a id="installation"></a>

## Installation

Die installierbare ZIP-Datei unter Plugins → Plugin hinzufügen → Plugin hochladen installieren. FluentCart → Tools for FluentCart öffnen, ein Preset wählen, Werte prüfen, speichern und Regeln aktivieren. Beide älteren Ein-Artikel-Add-ons müssen deaktiviert sein. Bei einer Neuinstallation sind die Regeln ausgeschaltet. Ohne FluentCart bleibt Cart Rules pausiert und ein Hinweis erscheint im Admin. Die Tools-Einstellungen sind dann unter Einstellungen → Tools for FluentCart erreichbar. Sobald FluentCart installiert und aktiviert ist, stehen sie im Shop-Menü.

<a id="features"></a>

## Funktionen

### Genau ein Artikel

Genau eine Produktvariante mit Menge 1. Ein neues Produkt ersetzt das bisherige. Vorhandene bearbeitbare Warenkörbe werden beim nächsten Laden auf ihre letzte Position reduziert. Entfernen bleibt möglich.

### Mindestmengen

Eine Mindestgesamtstückzahl und/oder eine Mindestmenge je Produktvariante festlegen. Verschiedene Produkte können gemeinsam die Mindestgesamtmenge erreichen.

### Mindestbestellwert

Es gilt die Shop-Währung. Gezählt wird der Warenwert nach Rabatten, ohne Versandkosten und Gebühren. Die Steuerbehandlung folgt den Produktpreisen in FluentCart. Ein Gratisprodukt erfüllt keinen positiven Mindestwert.

### Höchstmengen

Die Gesamtstückzahl und/oder die Stückzahl je Produktvariante begrenzen. Kunden können ihren Warenkorb frei anpassen; ungültige Warenkörbe können nicht bestellt werden.

### Mengenschritte

Jede ausgewählte Produktvariante muss eine durch den Mengenschritt teilbare Menge haben. Ein Schritt von 6 erlaubt 6, 12 oder 18, nicht 1, 7 oder 13. Grenzen werden gemeinsam geprüft, damit widersprüchliche Einstellungen nicht gespeichert werden können.

<a id="faq"></a>

## FAQ

### Wann greifen die Regeln?

Der Ein-Artikel-Modus passt bearbeitbare Warenkörbe automatisch an. Alle anderen Regeln werden beim Checkout geprüft. Hinweise erscheinen in den Standardansichten von Warenkorb und Checkout.

### Kann ich Presets kombinieren?

Presets füllen die Felder als Ausgangspunkt aus. Im Modus für eigene Regeln lassen sich Grenzen kombinieren. Der Ein-Artikel-Modus ist eigenständig und setzt andere Grenzen beim Speichern zurück.

### Was zählt als Produkt?

Jede Produktvariante zählt einzeln. Ein Bundle zählt als eine Warenkorbposition; seine Bestandteile werden nicht einzeln begrenzt.

### Begrenzt das Bestellungen pro Kunde?

Nein. Die Regeln gelten für jeden Warenkorb, ohne Kundenhistorie oder Kontolimits.

### Funktioniert das in Multisite?

Jede Website hat eigene Einstellungen. Netzwerkaktivierung erzeugt keine gemeinsame Bestellregel. Neue Websites starten mit ausgeschalteten Regeln.

### Welche Versionen und Integrationen werden unterstützt?

Dieses Plugin ist für WordPress ab 7.1.2, PHP ab 8.2 und FluentCart 1.7.x vorgesehen. Bei anderen FluentCart-Minorversionen bleibt die Anbindung inaktiv. Abos, Zusatzangebote, Bundles, eigene Shopansichten und weitere Warenkorb-Add-ons vor dem Live-Einsatz testen. Tools kann ohne FluentCart aktiviert werden; das Modul bleibt bis zur Aktivierung einer unterstützten Version pausiert.

### Was passiert bei der Deinstallation?

Einstellungen bleiben standardmäßig erhalten. Jede Website kann das Löschen ihrer Cart-Rules-Einstellungen erlauben. Temporäre Updater- und Library-Caches werden im jeweiligen Geltungsbereich bereinigt; Library-Daten erst beim letzten Host. FluentCart-Produkte, Bestellungen und Warenkörbe werden niemals gelöscht.

[Fragen nach Themen](Fragen-nach-Themen)

<a id="changelog"></a>

## Änderungsverlauf

### 0.9.0 · 2026-10-07

- **Neu:** Cart Rules mit Ein-Artikel-Warenkörben, Mindestmengen und Mindestbestellwert, Höchstmengen und Mengenschritten.

### 0.1.0–0.8.0

- **Sonstiges:** Entwicklungs- und Testversionen; nicht öffentlich veröffentlicht.

<a id="project"></a>

## Projekt

Entwicklung und Herausgabe: David Decker – DECKERWEB. Teil der Tools-Serie. Cart Rules ist das erste Modul.

<a id="security"></a>

## Sicherheit und Unterstützung

Sicherheitslücken vertraulich über Security → Report a vulnerability im Plugin-Repository melden. Plugin-, FluentCart-, WordPress- und PHP-Versionen, Schritte zur Reproduktion und Auswirkungen angeben, ohne Passwörter oder Kundendaten. Normale Fehler gehören in Issues. Sicherheitsdetails nicht in öffentlichen Issues veröffentlichen.

Fragen und normale Fehler: Issues im Repository. Vertrauliche Meldungen: Security. Unterstützung: https://ko-fi.com/deckerweb, https://buymeacoffee.com/daveshine, https://paypal.me/deckerweb.

<a id="license"></a>

## Lizenz

Copyright © 2026 David Decker – DECKERWEB. GPL v2 oder höher; SPDX: GPL-2.0-or-later. Eingebettete deckerweb Plugin Library 0.7.0 und deckerweb Updater 2.1.0 stammen vom selben Autor und stehen unter GPL-2.0-or-later. Ihre stabilen Laufzeitquellen sind unverändert übernommen; Host-Anbindung und Sprachkataloge liegen getrennt vor. Dieses Plugin liefert weder FluentCart-Implementierung noch Premium-Code aus.

