## Recruitment Task (EN Version, scroll down for PL)

This application is a small system for adding invoices (Invoice) to contractors (User). The system is in its initial development phase and allows for running two CLI commands.

```bash
# Create an invoice for the user user@example.com for the amount of 125.00 PLN
bin/console app:invoice:create user@example.com 12500

# Fetch the IDs of invoices that have the status "new" and an amount greater than 100.00 PLN
bin/console app:invoice:get-by-status-and-amount new 10000
```

### Existing Business Assumptions

*   The invoice amount must be greater than 0.
*   Amounts are stored in pennies (groszy).

# To-Do List

*   Extend the `User` entity by adding a parameter to specify the user's activity status (active/inactive). Create a migration that adds a new column to the database.
*   Add the ability to create a new user from the CLI. The argument is the user's email. A new user should be created with an "inactive" status.
*   After a user is created, an email should be sent to that user with the message: "An account has been registered in the system. Account activation may take up to 24h." - the goal is to use the `\App\Common\Mailer\MailerInterface`, not necessarily to implement actual email sending.
*   Introduce a business rule into the system that only allows creating invoices for active users and write tests to prove it works.
*   There is a bug in the system. The CLI Command `"app:invoice:get-by-status-and-amount"` for some reason returns all "new" invoices, ignoring the status and amount arguments. Find the cause and solve this problem.
*   Create a CLI Command to fetch the emails of inactive users.

## Running the Application

The application is configured to run with Docker images.

### The `.env` Configuration File

Rename the file `.env.example` to `.env`.

```bash
# Build the image and run the application container
docker-compose up -d

# List the running containers; find the CONTAINER ID in the list
docker ps

# Access the container's bash shell
docker exec -it {CONTAINER ID} bash
```

### Tests

```bash
bin/phpunit tests/Unit/
```

## Zadanie rekrutacyjne

Aplikacja jest małym systemem pozwalającym dodawać faktury (Invoice) do kontrahentów (User). System jest w początkowej fazie rozwoju i pozwala na uruchomienie dwóch poleceń z CLI.

```
# tworzenie faktury dla użytkownika user@example.com na kwotę 125,00 zł
bin/console app:invoice:create user@example.com 12500

# pobieranie identyfiaktorów faktur, które mają status "new" i ich kwota jest większa od 100,00 zł
bin/console app:invoice:get-by-status-and-amount new 10000
```

### Istniejące założenia biznesowe

- Kwota faktury musi być większa od 0
- Kwoty zapisywane są w groszach

# Do zrobienia

- Rozbuduj encję User dodając do niej parametr pozwalający określić aktywność użytkownika (aktywny/nieaktywny). Stwórz migrację, która utworzy nową kolumnę w bazie danych.
- Dodaj możliwość tworzenia nowego użytkownika z poziomu CLI. Argumentem jest e-mail. Nowy użytkownik powinien utworzyć się ze statusem nieaktywny.
- Po utworzeniu użytkownika powinien zostać wysłany e-mail do tego użytkownika z komunikatem "Zarejestrowano konto w systemie. Aktywacja konta trwa do 24h" - chodzi o wykorzystanie interfejsu \App\Common\Mailer\MailerInterface - nie trzeba tworzyć rzeczywistej wysyłki maila.
- Wprowadź do systemu założenie biznesowe pozwalające tworzyć faktury tylko dla aktywnych użytkowników i napisz testy udowadniające, że tak jest.
- W systemie jest błąd. CLI Command "app:invoice:get-by-status-and-amount" z jakiegoś powodu zwraca wszystkie nowe faktury ignorując argument statusu i kwoty. Znajdź przyczyny i rozwiąż ten problem
- Stwórz CLI Command do pobierania e-maili nieaktywnych użytkowników.

## Uruchomienie aplikacji

Aplikacja posiada konfigurację obrazów dockerowych

### Plik konfiguracyjny .env

Zmień nazwę pliku `.env.example` na `.env`.

```
# zbudowanie obrazu i uruchomienie kontenera aplikacji
docker-compose up -d

# lista uruchomionych kontenerów, na liście jest CONTAINER ID
docker ps

# wejście do bash kontenera
docker exec -it {CONTAINER ID} bash
```

### Testy

```
bin/phpunit tests/Unit/
```
