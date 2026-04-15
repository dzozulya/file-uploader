# File Storage Test Task

A Laravel-based web application for uploading and storing PDF/DOCX files with a limited retention period.

## Features

- Asynchronous upload of PDF and DOCX files
- Maximum file size: 10 MB
- Uploaded file metadata stored in MySQL
- Separate upload page
- Separate file management page
- Manual file deletion
- Automatic file deletion after 24 hours
- RabbitMQ event publishing after both manual and automatic deletion
- Example RabbitMQ consumer for handling deletion notifications
- Docker / Docker Compose support

## Tech Stack

- PHP 8.4
- Laravel
- MySQL 8
- RabbitMQ
- Bootstrap 5
- jQuery
- Docker Compose

## Pages

The application contains two separate pages:

- `/upload` — asynchronous file upload page
- `/files` — file management page with list and delete actions

## Architecture

- `FileController` — HTTP layer
- `StoreFileAction` — file upload use case
- `DeleteFileAction` — file deletion use case
- `RabbitMqPublisher` — publishes deletion events to RabbitMQ
- `DeleteExpiredFiles` — scheduled cleanup command
- `FileDeletedConsumer` — example consumer for deletion events
- `ConsumeFileDeletedMessages` — RabbitMQ consumer command

## Setup with Docker

### 1. Copy env file

```bash
cp .env.example .env
```
### 2. Start  and  build containers

```bash
docker compose up -d --build
```

### 3. Install dependencies

```bash
docker compose exec app composer install
```

### 4. Run migrations

```bash
docker compose exec app php artisan migrate
```

### 5.Generate app key
```bash
docker compose exec app php artisan key:generate
```

### 6. Create storage symlink

```bash
docker compose exec app php artisan storage:link
```
### 7.FixPermissions

```bash 
docker compose exec app chmod -R 777 storage bootstrap/cache
```

## SERVICES

### Application
```bash
http://localhost:8000
```

### RabbitMQ Management UI

* URL: http://localhost:15672
* Login: guest
* Password: guest

### Mysql
* Host: 127.0.0.1
* Port: 3307
* Database: file_uploader
* Username: test
* Password: test


### File Retention Logic
Each uploaded file gets an expires_at timestamp equal to uploaded_at + 24 hours.
Expired files are deleted by the scheduled Artisan command:
```bash
php artisan delete:expired-files`
```
This command:
* finds active expired files
* removes the physical file from storage
* marks the database record as deleted
* publishes a RabbitMQ event

### Scheduler

The scheduler runs the expired files cleanup command every minute.
To run it locally inside Docker:
```bash
docker compose exec app php artisan schedule:work
```
If you use the dedicated scheduler container from docker-compose.yml, it starts automatically.

### RabbitMQ Event Example

After file deletion the application publishes a message like this:
```json
{
  "event": "file_deleted",
  "file_id": 1,
  "original_name": "example.pdf",
  "stored_name": "uuid.pdf",
  "path": "uploads/uuid.pdf",
  "mime_type": "application/pdf",
  "extension": "pdf",
  "size": 12345,
  "reason": "manual",
  "notification_email": "test@example.com",
  "uploaded_at": "2026-04-15 10:00:00",
  "expires_at": "2026-04-16 10:00:00",
  "deleted_at": "2026-04-15 12:00:00"
}
```
reason can be:
* manual
* expired

### Consumer

An example RabbitMQ consumer is included in the project:
```bash
php artisan rabbitmq:consume-file-deleted
```
It validates incoming messages and logs that an email notification should be sent.
Actual SMTP email sending is intentionally not implemented, according to the task requirements.
If you use the dedicated worker container from docker-compose.yml, it starts automatically.

### Manual Testing
#### Upload flow
* Open http://localhost:8000/upload
* Select a PDF or DOCX file up to 10 MB
* Upload the file
* After successful upload, the user is redirected to /files
#### Manual delete flow
* Open http://localhost:8000/files
* Delete any file using the Delete button
* Verify that the file disappears from the list
* Verify that a RabbitMQ message is published
#### Auto-delete flow
* Change expires_at in database to a past datetime
* Run:
````bash
docker compose exec app php artisan files:delete-expired
````
* Verify that the file is deleted and a RabbitMQ event is published

### Notes
* File upload is asynchronous on the frontend via AJAX
* File deletion notifications are asynchronous via RabbitMQ
* Deleted files are physically removed from storage
* Database records are marked with deleted_at
* Real email delivery is outside the scope of this task

#### Possible Improvements
* File download action
* Pagination
* Feature tests
* Retry / dead-letter strategy for consumer side
* Separate mailer service

