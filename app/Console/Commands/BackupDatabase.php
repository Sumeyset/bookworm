<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Copies the database to storage/app/backups and keeps the newest N copies.
 * SQLite is copied directly; MySQL/MariaDB and PostgreSQL use their dump tools.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : How many backups to keep}';

    protected $description = 'Back up the database (run daily by the scheduler)';

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);
        $stamp = now()->format('Y-m-d_His');

        $file = match ($config['driver']) {
            'sqlite' => $this->sqlite($config, "{$dir}/bookworm_{$stamp}.sqlite"),
            'mysql', 'mariadb' => $this->dump(['mysqldump', '--single-transaction', '-h', $config['host'], '-P', (string) $config['port'], '-u', $config['username'], $config['database']], "{$dir}/bookworm_{$stamp}.sql", ['MYSQL_PWD' => (string) $config['password']]),
            'pgsql' => $this->dump(['pg_dump', '-h', $config['host'], '-p', (string) $config['port'], '-U', $config['username'], $config['database']], "{$dir}/bookworm_{$stamp}.sql", ['PGPASSWORD' => (string) $config['password']]),
            default => null,
        };

        if (! $file) {
            $this->error("Backups aren't supported for the {$config['driver']} driver.");

            return self::FAILURE;
        }

        // Keep only the newest backups.
        collect(File::files($dir))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->slice((int) $this->option('keep'))
            ->each(fn ($f) => File::delete($f->getPathname()));

        $this->info('Backup saved: ' . basename($file));

        return self::SUCCESS;
    }

    private function sqlite(array $config, string $target): ?string
    {
        $source = $config['database'];
        if (! is_file($source)) {
            return null;
        }
        copy($source, $target);

        return $target;
    }

    private function dump(array $command, string $target, array $env): ?string
    {
        $process = new Process($command, null, $env, null, 600);
        $process->run();
        if (! $process->isSuccessful()) {
            $this->error(trim($process->getErrorOutput()));

            return null;
        }
        file_put_contents($target, $process->getOutput());

        return $target;
    }
}
