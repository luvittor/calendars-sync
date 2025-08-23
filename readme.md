# Microsoft API Calendar Integration

This project provides PHP script examples to interact with the Microsoft Calendar API.

The scripts allow you to list calendars and manage events (list, create, update, and delete), as well as generate and renew authentication tokens.

## Initial Setup

### 1. Configure Azure and Obtain Parameters

1. Access the [Azure Portal](https://portal.azure.com/) and log in with your credentials.

2. Navigate to **Azure Active Directory** > **App registrations** and click **New registration** to register a new application.

3. Fill in the required information:
  - **Name**: Name of your application.
  - **Supported account types**: Choose who can use this application (usually "Accounts in this organizational directory only").
  - **Redirect URI**: Can be left blank for this project.

4. Click **Register**.

5. After registration, you will be redirected to the application page where you can obtain the following parameters:
  - **Application (client) ID**: This will be your `CLIENT_ID`.
  - **Directory (tenant) ID**: This will be your `TENANT_ID`.

6. To generate a `CLIENT_SECRET`, go to **Certificates & secrets** > **Client secrets** > **New client secret**. Copy the generated value and store it as `CLIENT_SECRET` in the `.env` file.

7. Enable the **Allow public client flows** option:
  - Go to **Authentication**.
  - In **Allow public client flows**, select **Yes**.

### 2. Set Parameters

1. **Copy the `.env-example` file to `.env`**:
   
  ```bash
  cp .env-example .env
  ```
   
  Then edit the `.env` file and fill in the parameters as needed.

2. **Required Parameters**:
   
  - **CLIENT_ID**: 
    - The Client ID of your application registered in [Azure](https://portal.azure.com/).
    - **How to obtain**: In the [Azure](https://portal.azure.com/) portal, go to **Azure Active Directory** > **App registrations** > select your application > copy the "Application (client) ID".

  - **CLIENT_SECRET**:
    - The client secret for authentication.
    - **How to obtain**: In the [Azure](https://portal.azure.com/) portal, go to **Azure Active Directory** > **App registrations** > select your application > **Certificates & secrets** > **Client secrets** > **New client secret**. Copy the generated value.
    - **Note**: Not required if you want authentication via digital certificate.

  - **CLIENT_SECRET_AUTH**:
    - Defines the authentication method.
    - **TRUE**: For authentication using `client_secret`.
    - **FALSE**: For authentication using `client_assertion` (digital certificate).

  - **TENANT_ID**:
    - The tenant ID of your organization in [Azure](https://portal.azure.com/).
    - **How to obtain**: In the [Azure](https://portal.azure.com/) portal, go to **Azure Active Directory** > copy the "Tenant ID".


   - **SCOPES**:
     - The permissions required to access the calendar.
     - **Default value**: `openid profile Calendars.Read Calendars.ReadWrite`.
     - **Note**: No need to change.

   - **CALENDAR_ID**:
     - The ID of the calendar where events will be created.
     - **Note**: Leave blank to use the default calendar. If you want to use a specific calendar, run `ms-calendar-list-all.php` after authentication to retrieve the ID.

3. **Authentication with `client_assertion` (digital certificate)**:
   
   If you choose to use `client_assertion`, you will need to generate a digital certificate.

   - **Certificate Generation**:
   
     In WSL or a Unix-like environment, run:

     ```bash
     openssl req -newkey rsa:2048 -nodes -keyout private.pem -x509 -days 365 -out public.pem
     ```

     This will generate two files: `private.pem` (private key) and `public.pem` (public certificate).

   - **Upload the Certificate to Azure**:
     
     In the [Azure](https://portal.azure.com/) portal, go to **Azure Active Directory** > **App registrations** > select your application > **Certificates & secrets** > **Certificates** > **Upload certificate**. Upload `public.pem`.

   - **File Configuration**:
     
     Move the `private.pem` and `public.pem` files to the `ms-auth-cert` directory inside the project.

### 3. Install Dependencies

Install the project dependencies using Composer:

```bash
composer install
```

### 4. Authentication

Before using the examples, you need to generate an access token.

- **Using `client_secret`**:

  If `CLIENT_SECRET_AUTH` is set to `TRUE` in the `.env` file, use the following command to authenticate:

  ```bash
  php ms-auth-client-secret.php
  ```

  The generated token will be saved in the `ms-auth-client-secret-token.json` file.

- **Using `client_assertion`**:

  If `CLIENT_SECRET_AUTH` is set to `FALSE`, use:

  ```bash
  php ms-auth-client-assertion.php
  ```

  The generated token will be saved in the `ms-auth-client-assertion-token.json` file.

## Usage Examples

### 1. List All Calendars

This script lists all calendars available in the account.

```bash
php ms-calendar-list-all.php
```

The list of calendars will be saved in `ms-calendar-list-all.json`.

### 2. Create an Event

Creates a new event in the specified calendar (or in the default calendar if `CALENDAR_ID` is not set).

```bash
php ms-calendar-event-create.php
```

The details of the created event will be saved in `ms-calendar-event-create.json`.

### 3. Update an Event

Updates an existing event based on the information stored in `ms-calendar-event-create.json`.

```bash
php ms-calendar-event-update.php
```

The updated details will be saved in the same JSON file.

### 4. Delete an Event

Deletes an existing event based on the information stored in `ms-calendar-event-create.json`.

```bash
php ms-calendar-event-delete.php
```

The event will be deleted and the JSON file will be removed.

### 5. List All Events

This script lists all events from the specified calendar (or the default calendar) and saves them in a JSON file.

```bash
php ms-calendar-event-list-all.php
```

The events will be saved in `ms-calendar-event-list-all.json`.

### 6. List Events from a Specific Date

Lists events scheduled from a specific date.

```bash
php ms-calendar-event-list-start-date.php
```

The events will be displayed on the screen.

### 7. List Events Modified After a Date

Lists events that were modified after a specific date.

```bash
php ms-calendar-event-list-modified-after.php
```

The events will be displayed on the screen.

## Final Considerations

These scripts provide a starting point for integrating with the Microsoft Calendar API. They can be adapted to meet specific needs.
