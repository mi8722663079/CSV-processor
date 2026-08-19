# Bulk CSV Import Service

A robust Laravel backend service designed to handle large-scale CSV product imports. This application emphasizes memory optimization and defensive data validation, ensuring that massive datasets and corrupted rows can be processed without causing server crashes (OOM) or database query exceptions.

## 🚀 Key Features

*   **Memory-Optimized Processing:** Utilizes `spatie/simple-excel` and PHP generators to stream files line-by-line. Combined with Laravel `LazyCollection` chunking, the server memory footprint remains flat regardless of file size.
*   **Defensive Data Validation:** Implements in-loop validation (`Validator::make`) to catch and isolate dirty data (e.g., missing fields, incorrect data types) before it reaches the database.
*   **High-Performance Bulk Inserts:** Validated records are mapped into 2D arrays and inserted via a single `Product::insert()` query per chunk, drastically reducing database connection overhead.
*   **Accurate Job Tracking:** Automatically tallies successful and failed records in real-time, providing immediate feedback for frontend consumption.

## 🛠️ Tech Stack & Architecture

*   **Framework:** Laravel
*   **Package:** `spatie/simple-excel` (Powered by OpenSpout for flat-memory file streaming)
*   **Database:** MySQL / PostgreSQL
*   **Pattern:** Job Queues (Chunked Processing)

## 💻 Prerequisites

Ensure your system has the following installed:
*   PHP 8.2+
*   Composer
*   MySQL 8.0+ or PostgreSQL

## ⚙️ Installation & Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Configure your environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update your `.env` file with your database credentials. 

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```

## ⚠️ Important Configuration: The Queue Driver

By default, the `.env` file for this project is configured to run background jobs synchronously for ease of local testing and debugging:

```env
QUEUE_CONNECTION=sync
```

**What this means:** When you upload a CSV, the `ProcessCsvImports` job will run immediately on the main PHP thread, freezing the UI until it finishes. 

**For Production / Large Files:** To see the true power of the background processing, change this to a proper queue driver (like `database` or `redis`) and run a dedicated worker terminal:

```env
QUEUE_CONNECTION=database
```
```bash
php artisan queue:work
```

## 🧪 Chaos Testing

This architecture is built to survive bad data. You can test its defensive capabilities by generating a dataset with intentionally corrupted rows:
1. Navigate to the `/generate-csv` route in your browser to generate a 1,000-row file with ~10% corrupted data.
2. Upload the file via the import endpoint.
3. Check the database or API response: you will see the valid rows successfully inserted, the failures accurately tallied, and zero fatal crashes.