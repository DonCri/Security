# Modul für Alarmierungen.

### Version
1.0

### Funktion
Bei aktivierung, durch Eingabe vom Password, wird der Status auf "Ein" gestellt. Bei Status Ein wird ein 15 Sekunden intervall aktiviert. 
Alle 15 Sekunden werden die ID's, welche in der Konfigurationsseite eingefügt wurden, überprüft. Sollte eine einen True Wert haben wird eine Push Nachricht gesendet.

### Konfigurationsseite
<b>WebFront:</b> WebFront wählen an welche die Push Nachrichten gesendet werden sollen. <br>
<b>Supplements:</b> Hier können die Variablen die überprüft werden sollen eingefügt werden. <b>Müssen Boolean Variablen sein.</b><br>
<b>Anzuzeigender Titel:</b> Titel für Push Nachrichten.<br>
<b>Anzuzeigender Text:</b> Text für Push Nachrichten.

