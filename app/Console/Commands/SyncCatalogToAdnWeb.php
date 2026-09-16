<?php

namespace App\Console\Commands;

use App\Models\CatalogItem;
use App\Services\AdnWebCatalogSyncService;
use Illuminate\Console\Command;

class SyncCatalogToAdnWeb extends Command
{
    protected $signature =
        'adn:sync-web-catalog
        {--id=* : Sincronizar solamente IDs específicos}';

    protected $description =
        'Sincroniza el catálogo maestro de ADN APP con ADN Web.';

    public function handle(
        AdnWebCatalogSyncService $service
    ): int {
        $query =
            CatalogItem::withTrashed()
                ->orderBy(
                    'id'
                );

        $ids =
            collect(
                $this->option(
                    'id'
                )
            )
                ->filter()
                ->map(
                    fn (
                        mixed $id
                    ): int =>
                        (int) $id
                )
                ->filter(
                    fn (
                        int $id
                    ): bool =>
                        $id >
                        0
                )
                ->unique()
                ->values();

        if (
            $ids->isNotEmpty()
        ) {
            $query->whereIn(
                'id',
                $ids
            );
        }

        $items =
            $query->get();

        if (
            $items->isEmpty()
        ) {
            $this->warn(
                'No hay productos para sincronizar.'
            );

            return self::SUCCESS;
        }

        $this->info(
            'Sincronizando '
            . $items->count()
            . ' producto(s)...'
        );

        $success =
            0;

        $failed =
            0;

        $bar =
            $this->output
                ->createProgressBar(
                    $items->count()
                );

        $bar->start();

        foreach (
            $items
            as $item
        ) {
            $synced =
                $service->sync(
                    $item
                );

            if (
                $synced
            ) {
                $success++;
            } else {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(
            2
        );

        $this->info(
            'Sincronizados: '
            . $success
        );

        if (
            $failed >
            0
        ) {
            $this->error(
                'Fallidos: '
                . $failed
            );

            return self::FAILURE;
        }

        $this->info(
            'Catálogo sincronizado correctamente.'
        );

        return self::SUCCESS;
    }
}