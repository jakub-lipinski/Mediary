# Mediary

<div align="center">

**Język / Language:** [🇵🇱 Polski](#polski) · [🇬🇧 English](#english)

</div>

---

<a id="polski"></a>
## 🇵🇱 Polski

Mediary to aplikacja webowa pomagająca uporządkować podstawowe informacje zdrowotne: wyniki badań, pomiary ciśnienia, wagę, dokumentację medyczną i plany żywieniowe. Projekt łączy panel użytkownika z automatycznym podsumowywaniem danych, ale główny nacisk pozostaje na czytelność, historię pomiarów i wygodną pracę z własnymi informacjami.

> Aplikacja ma charakter informacyjny i organizacyjny. Nie zastępuje konsultacji medycznej ani diagnozy specjalisty.

### Spis treści

- [Funkcje](#funkcje)
- [Stack](#stack)
- [Wymagania](#wymagania)
- [Uruchomienie lokalne](#uruchomienie-lokalne)
- [Praca z aplikacją](#praca-z-aplikacją)
- [Testy i formatowanie](#testy-i-formatowanie)
- [Build produkcyjny](#build-produkcyjny)
- [Bezpieczeństwo danych](#bezpieczeństwo-danych)

### Funkcje

- profil zdrowotny użytkownika: wiek, wzrost, waga, płeć i choroby przewlekłe;
- zapisywanie oraz analiza wyników badań krwi;
- dziennik pomiarów ciśnienia i wagi;
- przesyłanie dokumentów medycznych w formacie PDF lub DOCX;
- podgląd i podsumowania przesłanych plików;
- generowanie tygodniowych planów żywieniowych na podstawie profilu, preferencji i dokumentacji;
- notatki zdrowotne;
- logowanie standardowe, Google OAuth, 2FA i tokeny API.

### Stack

- Laravel 13, PHP 8.4
- Inertia.js 2 + Vue 3
- Tailwind CSS 4
- Laravel Jetstream, Fortify i Sanctum
- Laravel AI SDK dla agentów analizujących dane
- MySQL, database cache/session/queue
- PHPUnit 12
- Vite SSR build

### Wymagania

- PHP 8.4
- Composer
- Node.js i npm
- MySQL
- Laravel Herd lub równoważne lokalne środowisko
- Klucz `OPENAI_API_KEY`, jeśli funkcje analizy i generowania mają działać lokalnie
- Dane OAuth Google, jeśli używasz logowania przez Google

### Uruchomienie lokalne

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

### Praca z aplikacją

Po rejestracji użytkownik uzupełnia profil zdrowotny, a następnie może dodawać pomiary, wyniki badań i pliki. Dane z profilu są wykorzystywane jako kontekst przy tworzeniu podsumowań oraz planów żywieniowych.

Moduły analityczne działają przez dedykowanych agentów w `app/Ai/Agents`. Odpowiedzi są zwracane jako structured output, a HTML wygenerowany przez model jest dodatkowo czyszczony przed zapisem.

Dokumenty medyczne są przechowywane poza publicznym katalogiem. Po aktualizacji istniejącej instalacji uruchom jednorazowo:

```bash
php artisan medical-files:migrate-private
```

### Testy i formatowanie

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

Testy funkcji korzystających z agentów używają fake'ów Laravel AI SDK, dzięki czemu nie wykonują prawdziwych zapytań do zewnętrznych usług.

### Build produkcyjny

```bash
npm run build
```

Komenda buduje frontend oraz SSR bundle przez Vite.

### Bezpieczeństwo danych

Projekt blokuje przesyłanie plików zawierających oczywiste dane wrażliwe, takie jak PESEL, oraz odrzuca dokumenty, które nie wyglądają na medyczne. Mimo tego w środowiskach innych niż lokalne warto traktować konfigurację storage, logów i kopii zapasowych jako część bezpieczeństwa aplikacji.

<p align="right"><a href="#mediary">↑ Wróć na górę</a></p>

---

<a id="english"></a>
## 🇬🇧 English

Mediary is a web app that helps you organize basic health information: lab results, blood pressure, weight, medical documents and meal plans. It combines a user dashboard with automatic data summarization, focusing on readability, measurement history and convenient work with your own data.

> The app is informational and organizational only. It does not replace medical consultation or a professional diagnosis.

### Table of Contents

- [Features](#features)
- [Stack](#stack-1)
- [Requirements](#requirements)
- [Local Setup](#local-setup)
- [Working with the App](#working-with-the-app)
- [Tests & Formatting](#tests--formatting)
- [Production Build](#production-build)
- [Data Security](#data-security)

### Features

- health profile: age, height, weight, gender and chronic conditions;
- lab results storage and AI analysis;
- blood pressure and weight tracking;
- medical document upload (PDF or DOCX);
- preview and AI summaries of uploaded files;
- weekly meal plan generation based on profile, preferences and documents;
- health notes / journal;
- standard login, Google OAuth, 2FA and API tokens.

### Stack

- Laravel 13, PHP 8.4
- Inertia.js 2 + Vue 3
- Tailwind CSS 4
- Laravel Jetstream, Fortify & Sanctum
- Laravel AI SDK for analysis agents
- MySQL, database cache/session/queue
- PHPUnit 12
- Vite SSR build

### Requirements

- PHP 8.4
- Composer
- Node.js & npm
- MySQL
- Laravel Herd or equivalent local environment
- `OPENAI_API_KEY` if you want analysis/generation to work locally
- Google OAuth credentials if you use Google login

### Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

The project is pre-configured for Herd at:

```text
https://mediary.test
```

Key environment variables:

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

### Working with the App

After registration, fill in your health profile, then add measurements, lab results and files. Profile data is used as context for summaries and meal plans.

Analytical modules run via dedicated agents in `app/Ai/Agents`. Responses use structured output and any HTML generated by the model is sanitized before saving.

Medical documents are stored outside the public directory. After updating an existing installation, run once:

```bash
php artisan medical-files:migrate-private
```

### Tests & Formatting

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

Agent-related feature tests use Laravel AI SDK fakes, so no real external API calls are made.

### Production Build

```bash
npm run build
```

This builds the frontend and SSR bundle via Vite.

### Data Security

The app blocks uploads containing obvious sensitive data such as PESEL and rejects documents that don't look medical. In non-local environments, treat storage, logging and backup configuration as part of the security model.

<p align="right"><a href="#mediary">↑ Back to top</a></p>
