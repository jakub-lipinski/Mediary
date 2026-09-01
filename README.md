# Mediary

Mediary to aplikacja webowa pomagająca uporządkować podstawowe informacje zdrowotne: wyniki badań, pomiary ciśnienia, wagę, dokumentację medyczną i plany żywieniowe. Projekt łączy panel użytkownika z automatycznym podsumowywaniem danych, ale główny nacisk pozostaje na czytelność, historię pomiarów i wygodną pracę z własnymi informacjami.

> Aplikacja ma charakter informacyjny i organizacyjny. Nie zastępuje konsultacji medycznej ani diagnozy specjalisty.

## Funkcje

- profil zdrowotny użytkownika: wiek, wzrost, waga, płeć i choroby przewlekłe;
- zapisywanie oraz analiza wyników badań krwi;
- dziennik pomiarów ciśnienia i wagi;
- przesyłanie dokumentów medycznych w formacie PDF lub DOCX;
- podgląd i podsumowania przesłanych plików;
- generowanie tygodniowych planów żywieniowych na podstawie profilu, preferencji i dokumentacji;
- notatki zdrowotne;
- logowanie standardowe, Google OAuth, 2FA i tokeny API.

## Stack

- Laravel 13, PHP 8.4
- Inertia.js 2 + Vue 3
- Tailwind CSS 4
- Laravel Jetstream, Fortify i Sanctum
- Laravel AI SDK dla agentów analizujących dane
- MySQL, database cache/session/queue
- PHPUnit 12
- Vite SSR build

## Wymagania

- PHP 8.4
- Composer
- Node.js i npm
- MySQL
- Laravel Herd lub równoważne lokalne środowisko
- Klucz `OPENAI_API_KEY`, jeśli funkcje analizy i generowania mają działać lokalnie
- Dane OAuth Google, jeśli używasz logowania przez Google

## Uruchomienie lokalne

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

Domyślnie projekt jest przygotowany pod Herd i adres:

```text
https://mediary.test
```

Najważniejsze zmienne środowiskowe:

```env
APP_URL=https://mediary.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mediary
OPENAI_API_KEY=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=https://mediary.test/auth/google/callback
```

## Praca z aplikacją

Po rejestracji użytkownik uzupełnia profil zdrowotny, a następnie może dodawać pomiary, wyniki badań i pliki. Dane z profilu są wykorzystywane jako kontekst przy tworzeniu podsumowań oraz planów żywieniowych.

Moduły analityczne działają przez dedykowanych agentów w `app/Ai/Agents`. Odpowiedzi są zwracane jako structured output, a HTML wygenerowany przez model jest dodatkowo czyszczony przed zapisem.

Dokumenty medyczne są przechowywane poza publicznym katalogiem. Po aktualizacji istniejącej instalacji uruchom jednorazowo:

```bash
php artisan medical-files:migrate-private
```

## Testy i formatowanie

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

Testy funkcji korzystających z agentów używają fake'ów Laravel AI SDK, dzięki czemu nie wykonują prawdziwych zapytań do zewnętrznych usług.

## Build produkcyjny

```bash
npm run build
```

Komenda buduje frontend oraz SSR bundle przez Vite.

## Bezpieczeństwo danych

Projekt blokuje przesyłanie plików zawierających oczywiste dane wrażliwe, takie jak PESEL, oraz odrzuca dokumenty, które nie wyglądają na medyczne. Mimo tego w środowiskach innych niż lokalne warto traktować konfigurację storage, logów i kopii zapasowych jako część bezpieczeństwa aplikacji.
