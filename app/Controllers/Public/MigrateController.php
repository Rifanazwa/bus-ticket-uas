<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use CodeIgniter\Config\Services;
use Throwable;

class MigrateController extends BaseController
{
    /**
     * Runs database migrations and optionally seeders.
     * Accessible via: GET /migrate-db?token=<token>&action=<migrate|seed|reset>
     */
    public function index()
    {
        // Get the token from query parameter
        $token = $this->request->getGet('token');
        
        // Define secret token (prioritize environment variable, fallback to secure hardcoded string)
        $secretToken = env('MIGRATION_TOKEN');
        if (empty($secretToken)) {
            $secretToken = 'JossBusMigrateSecureToken_2026_xYz';
        }

        // Validate token
        if (empty($token) || $token !== $secretToken) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access Denied: Invalid migration token.');
        }

        $action = $this->request->getGet('action') ?? 'migrate';
        $output = '';

        try {
            $migrate = Services::migrations();

            if ($action === 'reset') {
                $output .= "Starting full database rollback/reset...<br>";
                // Regress to version 0 (rolls back all migrations)
                $migrate->regress(0);
                $output .= "Database successfully reset to state 0.<br>";
                
                // Migrate to latest
                $migrate->latest();
                $output .= "Database successfully migrated to latest version.<br>";
                
                // Seed database
                $output .= "Running DatabaseSeeder...<br>";
                $seeder = \Config\Database::seeder();
                $seeder->call('DatabaseSeeder');
                $output .= "DatabaseSeeder completed successfully.<br>";
            } elseif ($action === 'seed') {
                $output .= "Running DatabaseSeeder...<br>";
                $seeder = \Config\Database::seeder();
                $seeder->call('DatabaseSeeder');
                $output .= "DatabaseSeeder completed successfully.<br>";
            } else {
                $output .= "Running migrations to latest...<br>";
                // Run pending migrations
                $migrate->latest();
                $output .= "Migrations completed successfully.<br>";
            }

            return $this->response->setBody("
                <html>
                <head>
                    <title>Migration Success</title>
                    <style>
                        body { font-family: sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
                        h1 { color: #10b981; }
                        .log { background: #1e293b; padding: 1rem; border-radius: 0.5rem; border: 1px solid #334155; line-height: 1.5; }
                    </style>
                </head>
                <body>
                    <h1>DB Operation Success</h1>
                    <div class='log'>{$output}</div>
                </body>
                </html>
            ");

        } catch (Throwable $e) {
            return $this->response->setStatusCode(500)->setBody("
                <html>
                <head>
                    <title>Migration Failed</title>
                    <style>
                        body { font-family: sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
                        h1 { color: #ef4444; }
                        .error { background: #3b0712; border: 1px solid #7f1d1d; padding: 1rem; border-radius: 0.5rem; line-height: 1.5; color: #fecdd3; }
                        pre { background: #1e293b; padding: 1rem; border-radius: 0.5rem; border: 1px solid #334155; overflow-x: auto; color: #cbd5e1; }
                    </style>
                </head>
                <body>
                    <h1>DB Operation Failed</h1>
                    <div class='error'><strong>Error:</strong> " . esc($e->getMessage()) . "</div>
                    <h3>Stack Trace:</h3>
                    <pre>" . esc($e->getTraceAsString()) . "</pre>
                </body>
                </html>
            ");
        }
    }
}
