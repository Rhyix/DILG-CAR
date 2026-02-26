# Queue Worker Auto-Start (Windows)

To keep email notifications asynchronous and fast, run the queue worker continuously.

## One-time install (auto-start on login)

```powershell
powershell -ExecutionPolicy Bypass -File scripts\install-queue-worker-startup.ps1
```

This creates a startup launcher in your Windows Startup folder.

## Remove auto-start

```powershell
powershell -ExecutionPolicy Bypass -File scripts\uninstall-queue-worker-startup.ps1
```

## Manual run (current session only)

```powershell
scripts\run-queue-worker.cmd
```

## Notes

- Ensure `.env` uses `QUEUE_CONNECTION=database`.
- Worker command used:
  `php artisan queue:work --queue=default --sleep=1 --tries=3 --timeout=120 --memory=512`

