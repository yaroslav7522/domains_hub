<?php

namespace App\Console\Commands;

use App\Jobs\CheckDomainJob;
use App\Models\Domain;
use Illuminate\Console\Command;

class CheckDomainsCommand extends Command
{
    protected $signature = 'domains:check {--id= : Check a single domain by ID}';

    protected $description = 'Dispatch check jobs for all domains (or a single domain)';

    public function handle(): int
    {
        $query = Domain::query();

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $domains = $query->get();

        if ($domains->isEmpty()) {
            $this->warn('No domains found.');
            return self::SUCCESS;
        }

        foreach ($domains as $domain) {
            CheckDomainJob::dispatch($domain);
            $this->line("  Queued: {$domain->domain}");
        }

        $this->info("Dispatched {$domains->count()} check job(s).");

        return self::SUCCESS;
    }
}
