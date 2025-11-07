
# Wedding Upload Platform

Eine moderne Foto- und Video-Plattform auf Basis von Laravel (Backend -API)
und Vue.js (Frontend), ausgeführt über Herd (lokal) und Hetzner Cloud (Produktivumgebung).
Benutzer können Alben erstellen, QR-Codes generieren und Medien exportieren.
Gäste können über QR-Code und PIN auf ein Album zugreifen und Dateien hochladen.

## Funktionen

Besitzer
> Registrierung & Login
> Album erstellen, bearbeiten, löschen
> QR-Code-Generierung
> PIN-Vergabe für Gäste
> ZIP-Export mit Fortschrittsanzeige

Gäste
> Zugriff über QR-Code-Link (/guest/:albumId)
> PIN-Eingabe zur Verifikation
> Zeitfristiger Zugriff (Token über Redis, 24 St. gültig)
> Fotos & Videos anzeigen oder hochladen





## License

