<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateShortUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-short-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyingkat URL Rating Remote dan SKD untuk data lama yang belum memiliki shortlink';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai migrasi shortlink...');
        
        $bukuTamus = \App\Models\BukuTamu::where('rating_short_url', 'NOT LIKE', '%is.gd%')
            ->orWhere(function($q) {
                $q->whereNotNull('skd_token')->where('skd_short_url', 'NOT LIKE', '%is.gd%');
            })
            ->get();

        if ($bukuTamus->isEmpty()) {
            $this->info('Tidak ada data yang perlu dimigrasi.');
            return;
        }

        $bar = $this->output->createProgressBar(count($bukuTamus));
        $bar->start();

        foreach ($bukuTamus as $bt) {
            $updated = false;

            // Rating Remote
            if (!$bt->rating_short_url) {
                $gasUrl = config('services.gas.rating_url');
                if ($gasUrl) {
                    $longRatingUrl = $gasUrl . (str_contains($gasUrl, '?') ? '&' : '?') . "token=" . $bt->rating_token;
                    $bt->rating_short_url = \App\Helpers\UrlHelper::shorten($longRatingUrl);
                    $updated = true;
                }
            }

            // SKD
            if ($bt->skd_token && !$bt->skd_short_url) {
                $gasSkdUrl = config('services.gas.skd_url') ?? "https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec";
                $longSkdUrl = $gasSkdUrl . (str_contains($gasSkdUrl, '?') ? '&' : '?') . "token=" . $bt->skd_token;
                $bt->skd_short_url = \App\Helpers\UrlHelper::shorten($longSkdUrl);
                $updated = true;
            }

            if ($updated) {
                $bt->save();
            }

            $bar->advance();
            // Sleep briefly to avoid hitting TinyURL rate limits if many records
            usleep(200000); // 0.2 seconds
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migrasi shortlink selesai!');
    }
}
