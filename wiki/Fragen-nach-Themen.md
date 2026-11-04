# Fragen nach Themen

[English](FAQ-by-topic)

## Einstieg und Alltag

### Ersetzt das Plugin FluentCart?

Nein. Es ist ein Add-on für eine aktive FluentCart-Installation. Produkte, Warenkörbe, Kunden, Bestellungen und Zahlungen verwaltet FluentCart. Tools ergänzt optionale Shophelfer.

### Mit welchem Modul beginne ich?

Für Bestellgrenzen mit Warenkorb-Regeln, für erklärende Produkttexte mit Produkthinweisen und für Seitenzuordnungen mit der Shop-Prüfung. Der Katalogmodus hält Produkte sichtbar und pausiert neue Onlinekäufe.

### Kann ich Regeln auf bestimmte Produkte beschränken?

Ja. Eigene Mengen- und Wertgrenzen können ausgewählte Hauptprodukte und alle ihre Varianten zählen. Andere Positionen tragen nicht dazu bei. Der Ein-Artikel-Modus gilt immer für den ganzen Warenkorb. Produkt-IDs gehören zur jeweiligen Website.

### Ändern sich vorhandene Regeln beim Update?

Bestehende Warenkorb-Regeln bleiben erhalten. Neue Module sind ausgeschaltet. Eine leere Produktauswahl erhält den bisherigen Geltungsbereich für den ganzen Warenkorb. Importierte Module bleiben bewusst bis zur Kontrolle ausgeschaltet.

## Shopansichten und Grenzen

### Was sperrt der Katalogmodus?

Native Warenkorbkäufe und Checkout, einschließlich direkter Checkout-Links. Native Kaufbereiche werden bei unterstütztem Renderer entfernt; für ältere Ansichten gibt es einen begrenzten CSS-Fallback. Daten bleiben erhalten. Eigene Layouts, Caches und externe Zahlungswege gesondert prüfen.

### Was passiert ohne FluentCart?

Das Plugin bleibt im Admin aktiv; seine Einstellungen liegen dann unter WordPress-Einstellungen. Abhängige Module pausieren und erhalten ihre Werte. Regel- und Moduländerungen werden auf dem Server blockiert; Export und Löschoptionen bleiben verfügbar.

### Funktioniert es in Multisite?

Die Einstellungen gelten getrennt je Website. FluentCart 1.7.0, 2.2.0 und 2.3.0 haben beim Anlegen neuer Websites einen Scheduler-Fehler. Tools dokumentiert ihn und verändert den FluentCart-Code nicht.

### Sind die Produkthinweise rechtliche Checkout-Hinweise?

Nein. Sie sind für normale Produktinformationen gedacht. Tools liefert keine Rechtstexte, keine Zertifizierung und keinen Ersatz für spezialisierte Checkout-Rechtslösungen.

### Kann ein Hinweis HTML oder Shortcodes enthalten?

Nein. Hinweise sind einfacher Text mit Zeilenumbrüchen. Den dafür vorgesehenen Produkthinweis-Shortcode kannst du in eigenen Layouts platzieren.

### Warum meldet die Shop-Prüfung bei einer funktionierenden Builder-Seite Prüfen?

Die Prüfung erkennt native Shortcodes und FluentCart-Blöcke im Seiteninhalt. Builder oder Templates können die Ausgabe andernorts erzeugen. Öffne die Seite und kontrolliere den Ablauf manuell.

### Prüft der Beispieltest Zahlungen?

Nein. Er prüft eine Beispielposition gegen gespeicherte Regeln, ohne echten Warenkorb, Bestellung, Bestandsprüfung oder Zahlung.

## Daten und Verwaltung

### Gibt es Bestelllimits pro Kunde?

Nein. Warenkorb-Regeln prüfen den aktuellen Warenkorb, keine früheren Bestellungen oder Kundenhistorien.

### Was zählt als Produkt?

Jede Variante ist eine eigene Warenkorbposition. Ein Bundle zählt als eine Position; enthaltene Komponenten werden nicht einzeln begrenzt.

### Was verlässt meine Website?

Die neuen Module stellen keine externen Anfragen. Der GitHub-Updater prüft dieses Repository; die gemeinsame Library hat einen eigenen optionalen Onlinekatalog und Installationsablauf. Diese Verbindungen sind unter Daten und Datenschutz beschrieben. Tools exportiert keine Bestell- oder Kundendaten.

