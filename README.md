
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

## Technologien

> Laravel 12, PHP 8.2
> Vue3 + Vite
> MySQL - ( Herd / Hetzner Cloud DB)
> Redis - Speicherung von Token & Exportstatus
> Bootstrap / Tailwind - Benutzeroberfläche
> Laravel Queues -ZIP-Erstellung im Hintergrund
> Deployment auf Hetzner Cloud Server (Ubuntu)

## Lokale Installation (Herd)

1. Projekt liegt unter:
   C:\\User\VOsmanova\Herd\wedding-upload

2. Befehle in Terminal
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
npm run dev

3. Redis starten:

redis-server

4. Lokale URL:
   https://wedding-upload.test

## Ablauf (Gastzugriff)

1. Besitzer erstellt Album mit PIN.
2. Gast scannt QR-Code -> /guest/:albumId.
3. Gast gibt PIN ein -> Token wird in Redis gespeichert (24 h gültig).
4. Mit Token kann Gast Medien sehen oder hochladen.

## Wichtige Befehle

    Aktion                          Befehl
Migrationen                     php artisan migrate
Cache leeren                    php artisan cache:clear
Queue starten                   php artisan queue:work

## Haupt-Kontrollern

> AuthController - Login & Registrierung
> AlbumController - Albenverwaltung & Export
> GuestAlbumController - Gastzugriff & PIN-Prüfung
> ExportAlbumJob - ZIP-Erstellung im Hintergrund


