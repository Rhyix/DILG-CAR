<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class BackupRestoreController extends Controller
{
    private const BACKUP_REMINDER_DAYS = 30;

    private function latestBackupAt(): ?Carbon
    {
        $latestBackup = Activity::query()
            ->where('event', 'backup')
            ->latest('created_at')
            ->first(['created_at']);

        if (!$latestBackup || empty($latestBackup->created_at)) {
            return null;
        }

        return $latestBackup->created_at instanceof Carbon
            ? $latestBackup->created_at
            : Carbon::parse((string) $latestBackup->created_at);
    }

    private function buildBackupReminderState(): array
    {
        $latestBackupAt = $this->latestBackupAt();
        if (!$latestBackupAt) {
            return [
                'latest_backup_at' => null,
                'days_since_last_backup' => null,
                'is_overdue' => true,
                'status_label' => 'No backup record found',
                'reminder_message' => 'No successful backup record was found. Please generate a full system backup now.',
            ];
        }

        $daysSinceLastBackup = $latestBackupAt->diffInDays(now());
        $isOverdue = $daysSinceLastBackup >= self::BACKUP_REMINDER_DAYS;

        return [
            'latest_backup_at' => $latestBackupAt,
            'days_since_last_backup' => $daysSinceLastBackup,
            'is_overdue' => $isOverdue,
            'status_label' => $isOverdue
                ? 'Backup overdue'
                : 'Backup status healthy',
            'reminder_message' => $isOverdue
                ? "The last successful backup was {$daysSinceLastBackup} day(s) ago. Backup is required to protect system data."
                : 'Backup cadence is within the recommended interval.',
        ];
    }

    private function notifyOverdueBackupReminder(array $backupReminder): void
    {
        if (!(bool) ($backupReminder['is_overdue'] ?? false)) {
            return;
        }

        $admin = Auth::guard('admin')->user();
        if (!$admin || ($admin->role ?? null) !== 'superadmin') {
            return;
        }

        $alreadyNotifiedToday = Notification::query()
            ->where('notifiable_type', Admin::class)
            ->where('notifiable_id', $admin->id)
            ->where('data->category', 'backup_reminder')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyNotifiedToday) {
            return;
        }

        Notification::create([
            'notifiable_type' => Admin::class,
            'notifiable_id' => $admin->id,
            'type' => 'warning',
            'data' => [
                'category' => 'backup_reminder',
                'title' => 'Backup Reminder',
                'message' => (string) ($backupReminder['reminder_message'] ?? 'Backup is required to protect system data.'),
                'action_url' => route('admin.backup.index', [], false),
                'level' => 'warning',
            ],
        ]);
    }

    public function index()
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }

        $backupReminder = $this->buildBackupReminderState();
        $this->notifyOverdueBackupReminder($backupReminder);

        return view('admin.backup_restore', [
            'backupReminder' => $backupReminder,
        ]);
    }

    public function backup(Request $request)
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        try {
            $conn = config('database.default');
            $cfg = config("database.connections.$conn");
            $db = $cfg['database'];
            $host = $cfg['host'] ?? '127.0.0.1';
            $port = $cfg['port'] ?? 3306;
            $user = $cfg['username'];
            $pass = $cfg['password'];

            $dir = storage_path('app/backups');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $file = $dir . DIRECTORY_SEPARATOR . $db . '-' . now()->format('Ymd-His') . '.sql';

            $dumpPath = env('MYSQLDUMP_PATH');
            if (!$dumpPath) {
                $candidates = [
                    'C:\xampp\mysql\bin\mysqldump.exe',
                    'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
                    '/usr/bin/mysqldump',
                    '/usr/local/bin/mysqldump',
                ];
                foreach ($candidates as $c) {
                    if (is_file($c)) {
                        $dumpPath = $c;
                        break;
                    }
                }
            }

            $usedNative = false;
            $nativeTried = false;
            if ($dumpPath && is_file($dumpPath)) {
                $nativeTried = true;
                // Attempt 1: capture stdout and write to file (works across OSes and avoids shell redirection issues)
                $cmdBase = '"' . $dumpPath . '"' .
                    ' --host=' . $host .
                    ' --port=' . (string)$port .
                    ' --user=' . $user .
                    ' --password=' . $pass .
                    ' --routines --triggers --events --single-transaction --quick ' .
                    $db;
                try {
                    $output = shell_exec($cmdBase);
                    if (!empty($output)) {
                        file_put_contents($file, $output);
                    }
                } catch (\Throwable $e) {
                    Log::warning('mysqldump capture failed: ' . $e->getMessage());
                }
                $usedNative = file_exists($file) && filesize($file) > 0;

                // Attempt 2: shell redirection to file
                if (!$usedNative) {
                    $redirCmd = $cmdBase . ' > "' . $file . '"';
                    try {
                        if (stripos(PHP_OS, 'WIN') === 0) {
                            pclose(popen('cmd /c ' . $redirCmd, 'r'));
                        } else {
                            shell_exec($redirCmd);
                        }
                    } catch (\Throwable $e) {
                        Log::warning('mysqldump redirection failed: ' . $e->getMessage());
                    }
                    $usedNative = file_exists($file) && filesize($file) > 0;
                }
            }

            if (!$usedNative) {
                Log::info('Falling back to PHP-based SQL dump', ['native_tried' => $nativeTried, 'dumpPath' => $dumpPath]);
                $pdo = DB::connection()->getPdo();
                $tables = [];
                foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                    $vals = array_values((array)$row);
                    if (!empty($vals[0])) $tables[] = $vals[0];
                }
                $out = "-- SQL Backup generated at " . now()->toDateTimeString() . "\n";
                $out .= "SET FOREIGN_KEY_CHECKS=0;\n";
                $out .= "START TRANSACTION;\n\n";
                foreach ($tables as $t) {
                    $createRow = DB::select("SHOW CREATE TABLE `$t`")[0] ?? null;
                    if (!$createRow) continue;
                    // Handle different key names returned by MySQL
                    $arrRow = (array)$createRow;
                    $create = $arrRow['Create Table'] ?? $arrRow['Create Table'] ?? $arrRow['Create View'] ?? null;
                    if (!$create) continue;
                    $out .= "DROP TABLE IF EXISTS `$t`;\n$create;\n\n";
                    $rows = DB::table($t)->select('*')->get();
                    if ($rows->isEmpty()) continue;
                    $chunks = $rows->chunk(200);
                    foreach ($chunks as $chunk) {
                        $cols = array_map(fn($c) => "`$c`", array_keys((array)$chunk->first()));
                        $out .= "INSERT INTO `$t` (" . implode(',', $cols) . ") VALUES\n";
                        $lines = [];
                        foreach ($chunk as $r) {
                            $vals = [];
                            foreach ((array)$r as $v) {
                                if (is_null($v)) $vals[] = 'NULL';
                                else {
                                    $vals[] = $pdo->quote((string)$v);
                                }
                            }
                            $lines[] = '(' . implode(',', $vals) . ')';
                        }
                        $out .= implode(",\n", $lines) . ";\n\n";
                    }
                }
                $out .= "COMMIT;\n";
                $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
                file_put_contents($file, $out);
            }

            if (!file_exists($file) || filesize($file) === 0) {
                throw new \RuntimeException('Backup output is empty. Ensure mysqldump is installed or fallback had permissions.');
            }

            activity()
                ->event('backup')
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties([
                    'section' => 'Backup & Restore',
                    'backup_file' => basename($file),
                    'backup_generated_at' => now()->toDateTimeString(),
                ])
                ->log('Generated database backup.');

            return response()->download($file)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Backup error: ' . $e->getMessage());
            return back()->withErrors(['msg' => 'Backup failed: ' . $e->getMessage()]);
        }
    }

    public function restore(Request $request)
    {
        if ((Auth::guard('admin')->user()->role ?? null) !== 'superadmin') {
            abort(403);
        }
        $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt|max:524288',
        ]);
        try {
            $file = $request->file('sql_file')->getRealPath();
            if (!is_file($file) || filesize($file) === 0) {
                throw new \RuntimeException('Uploaded SQL file is empty or unreadable.');
            }
            $uploadedFileName = (string) ($request->file('sql_file')->getClientOriginalName() ?? '');
            $conn = config('database.default');
            $cfg = config("database.connections.$conn");
            $db = $cfg['database'];
            $host = $cfg['host'] ?? '127.0.0.1';
            $port = $cfg['port'] ?? 3306;
            $user = $cfg['username'];
            $pass = $cfg['password'];

            $mysqlPath = env('MYSQL_CLI_PATH');
            if (!$mysqlPath) {
                $candidates = [
                    'C:\xampp\mysql\bin\mysql.exe',
                    'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
                    '/usr/bin/mysql',
                    '/usr/local/bin/mysql',
                ];
                foreach ($candidates as $c) {
                    if (is_file($c)) {
                        $mysqlPath = $c;
                        break;
                    }
                }
            }

            $usedNative = false;
            if ($mysqlPath && is_file($mysqlPath)) {
                $cmd = '"' . $mysqlPath . '"' .
                    ' --host=' . $host .
                    ' --port=' . (string)$port .
                    ' --user=' . $user .
                    ' --password=' . $pass .
                    ' ' . $db;
                $descriptors = [
                    0 => ['pipe', 'r'], // stdin
                    1 => ['pipe', 'w'], // stdout
                    2 => ['pipe', 'w'], // stderr
                ];
                $proc = proc_open($cmd, $descriptors, $pipes);
                if (is_resource($proc)) {
                    $sql = file_get_contents($file);
                    fwrite($pipes[0], $sql);
                    fclose($pipes[0]);
                    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
                    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
                    $exit = proc_close($proc);
                    if ($exit !== 0) {
                        Log::warning('mysql client restore returned non-zero exit', ['exit' => $exit, 'stderr' => $stderr]);
                    } else {
                        $usedNative = true;
                    }
                } else {
                    Log::warning('Failed to start mysql client process for restore');
                }
            }

            if (!$usedNative) {
                Log::info('Falling back to PHP-based SQL restore');
                $handle = fopen($file, 'r');
                if (!$handle) {
                    throw new \RuntimeException('Failed to read file');
                }
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                $statement = '';
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/^\s*--/', $line)) {
                        continue;
                    }
                    $statement .= $line;
                    if (substr(rtrim($line), -1) === ';') {
                        DB::unprepared($statement);
                        $statement = '';
                    }
                }
                fclose($handle);
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            activity()
                ->event('restore')
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties([
                    'section' => 'Backup & Restore',
                    'restore_file' => $uploadedFileName,
                    'restore_executed_at' => now()->toDateTimeString(),
                ])
                ->log('Restored database from uploaded backup.');

            return redirect()->route('admin.backup.index')->with('success', 'Database restored successfully.');
        } catch (\Throwable $e) {
            Log::error('Restore error: ' . $e->getMessage());
            return redirect()->route('admin.backup.index')->withErrors(['msg' => 'Restore failed: ' . $e->getMessage()]);
        }
    }
}
