# Wiki Donate Backend

This Laravel backend provides the API services for the Wiki Donate application. It includes endpoints for handling user authentication, page creation and editing, version control, and other core functionalities required for a wiki site.

## Features

-   **User Authentication**: Manage user registrations, logins, and secure access to contribute to the wiki.
-   **API for Page Creation and Editing**: Expose endpoints to create and edit wiki pages, utilizing version control to store and retrieve page history.
-   **Search Functionality**: API endpoints to support search queries for finding content efficiently.
-   **Category and Tag Management**: Organize wiki pages by categories and tags, facilitating navigation and filtering.
-   **Discussion Pages**: Implement discussion functionality for articles to promote community engagement.

## Prerequisites

-   **PHP 8.1+**
-   **Laravel 11**
-   **Composer**
-   **Database** (MySQL)

## Install Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

## Environment Configuration

Copy the .env.example file to create a new .env file:

```bash
cp .env.example .env
```

## Generate Application Key

```bash
php artisan key:generate
```

## Database Migration and Seeding

```bash
php artisan migrate --seed
```

## Start the Server

The API will be accessible at `http://localhost:8000`

```bash
php artisan serve
```
