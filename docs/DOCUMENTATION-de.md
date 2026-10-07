# Dokumentation

Die installierbare ZIP-Datei unter Plugins → Plugin hinzufügen → Plugin hochladen installieren. FluentCart zuerst aktivieren. Einstellungen → Tools for FluentCart öffnen, ein Preset wählen, Werte prüfen, speichern und Regeln aktivieren. Beide älteren Ein-Artikel-Add-ons müssen deaktiviert sein. Bei einer Neuinstallation sind die Regeln ausgeschaltet.

## Genau ein Artikel

Genau eine Produktvariante mit Menge 1. Ein neues Produkt ersetzt das bisherige. Vorhandene bearbeitbare Warenkörbe werden beim nächsten Laden auf ihre letzte Position reduziert. Entfernen bleibt möglich.

## Mindestmengen

Eine Mindestgesamtstückzahl und/oder eine Mindestmenge je Produktvariante festlegen. Verschiedene Produkte können gemeinsam die Mindestgesamtmenge erreichen.

## Mindestbestellwert

Es gilt die Shop-Währung. Gezählt wird der Warenwert nach Rabatten, ohne Versandkosten und Gebühren. Die Steuerbehandlung folgt den Produktpreisen in FluentCart. Ein Gratisprodukt erfüllt keinen positiven Mindestwert.

## Höchstmengen

Die Gesamtstückzahl und/oder die Stückzahl je Produktvariante begrenzen. Kunden können ihren Warenkorb frei anpassen; ungültige Warenkörbe können nicht bestellt werden.

## Mengenschritte

Jede ausgewählte Produktvariante muss eine durch den Mengenschritt teilbare Menge haben. Ein Schritt von 6 erlaubt 6, 12 oder 18, nicht 1, 7 oder 13. Grenzen werden gemeinsam geprüft, damit widersprüchliche Einstellungen nicht gespeichert werden können.

Keine eigenen öffentlichen Hooks oder REST-Endpunkte. Diese Version nutzt FluentCart-Hooks und Modellereignisse sowie die WordPress Settings API. Die FluentCart-Anbindung ist bewusst auf die geprüfte 1.7.x-Schnittstelle begrenzt.

Gespeichert wird eine Website-Option: tffc_cart_rules. Keine Kundendaten, zusätzlichen Datenbanktabellen, Analyse oder Tracking. Keine Cart-Rules-Hintergrundaufgaben. Gemeinsame Library-Einstellungen und Caches haben ihren dokumentierten Website-/Netzwerk-Geltungsbereich. Deaktivierung erhält Einstellungen. Automatische Ein-Artikel-Ersetzungen werden durch Deaktivierung nicht rückgängig gemacht.

Der eingebettete deckerweb Updater prüft öffentliche GitHub-Releases im WordPress-Updateablauf. GitHub erhält dabei die Netzwerkadresse der Installation und gewöhnliche HTTP-Metadaten; keine Zugangsdaten oder Kundenwarenkörbe. Der freiwillige gemeinsame Library-Onlinekatalog ist zunächst ausgeschaltet. Nach Aktivierung lädt er freigegebene öffentliche Metadaten von raw.githubusercontent.com. Gestaltung und Übersetzungen werden lokal ausgeliefert.
