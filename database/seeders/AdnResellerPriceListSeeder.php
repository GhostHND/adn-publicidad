<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class AdnResellerPriceListSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('catalog_items')) {
            throw new RuntimeException(
                'No existe la tabla catalog_items.'
            );
        }

        $columns =
            Schema::getColumnListing(
                'catalog_items'
            );

        $created = 0;
        $updated = 0;

        $priceList =
            $this->priceList();

        foreach (
            $priceList
            as $index => $item
        ) {
            $existingId =
                $this->findExistingItemId(
                    columns: $columns,
                    code: $item['code'],
                    name: $item['name'],
                );

            $payload =
                $this->payload(
                    item: $item,
                    columns: $columns,
                    sortOrder:
                        ($index + 1) * 10,
                );

            if ($existingId) {
                if (
                    $this->hasColumn(
                        $columns,
                        'updated_at'
                    )
                ) {
                    $payload['updated_at'] =
                        now();
                }

                DB::table(
                    'catalog_items'
                )
                    ->where(
                        'id',
                        $existingId
                    )
                    ->update(
                        $payload
                    );

                $updated++;

                continue;
            }

            if (
                $this->hasColumn(
                    $columns,
                    'created_at'
                )
            ) {
                $payload['created_at'] =
                    now();
            }

            if (
                $this->hasColumn(
                    $columns,
                    'updated_at'
                )
            ) {
                $payload['updated_at'] =
                    now();
            }

            DB::table(
                'catalog_items'
            )->insert(
                $payload
            );

            $created++;
        }

        $this->command?->newLine();

        $this->command?->info(
            'Lista de precios ADN cargada correctamente.'
        );

        $this->command?->line(
            "Productos creados: {$created}"
        );

        $this->command?->line(
            "Productos actualizados: {$updated}"
        );

        $this->command?->line(
            'Productos del tarifario: '
            .
            count(
                $priceList
            )
        );
    }

    /**
     * Buscar primero por código oficial y luego
     * por nombre para evitar duplicados.
     *
     * @param array<int, string> $columns
     */
    private function findExistingItemId(
        array $columns,
        string $code,
        string $name,
    ): ?int {
        if (
            $this->hasColumn(
                $columns,
                'item_code'
            )
        ) {
            $id =
                DB::table(
                    'catalog_items'
                )
                    ->where(
                        'item_code',
                        $code
                    )
                    ->value(
                        'id'
                    );

            if ($id) {
                return (int) $id;
            }
        }

        if (
            $this->hasColumn(
                $columns,
                'name'
            )
        ) {
            $id =
                DB::table(
                    'catalog_items'
                )
                    ->whereRaw(
                        'LOWER(TRIM(name)) = ?',
                        [
                            mb_strtolower(
                                trim(
                                    $name
                                )
                            ),
                        ]
                    )
                    ->value(
                        'id'
                    );

            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * Construir el registro utilizando únicamente
     * las columnas existentes.
     *
     * @param array<string, mixed> $item
     * @param array<int, string> $columns
     * @return array<string, mixed>
     */
    private function payload(
        array $item,
        array $columns,
        int $sortOrder,
    ): array {
        $payload = [];

        $set =
            function (
                string $column,
                mixed $value
            ) use (
                &$payload,
                $columns
            ): void {
                if (
                    $this->hasColumn(
                        $columns,
                        $column
                    )
                ) {
                    $payload[
                        $column
                    ] =
                        $value;
                }
            };

        $method =
            strtoupper(
                (string)
                $item['method']
            );

        $cost =
            (float)
            $item['cost'];

        $public =
            (float)
            $item['public'];

        /*
        |--------------------------------------------------------------------------
        | IDENTIDAD
        |--------------------------------------------------------------------------
        */

        $set(
            'item_code',
            $item['code']
        );

        $set(
            'sku',
            $item['code']
        );

        $set(
            'name',
            $item['name']
        );

        $set(
            'slug',
            Str::slug(
                $item['name']
            )
        );

        /*
        |--------------------------------------------------------------------------
        | CLASIFICACIÓN
        |--------------------------------------------------------------------------
        |
        | IMPORTANTE:
        |
        | item_type describe qué ES el elemento.
        | pricing_method describe CÓMO se calcula.
        |
        | Por eso estos elementos son product.
        | RESALE no debe utilizarse como item_type.
        |
        */

        $set(
            'item_type',
            'product'
        );

        $set(
            'category',
            'Lista de precios'
        );

        $set(
            'pricing_method',
            $method
        );

        $set(
            'measurement_unit',
            $item['unit']
        );

        $set(
            'unit_code',
            $item['unit']
        );

        /*
        |--------------------------------------------------------------------------
        | DESCRIPCIÓN
        |--------------------------------------------------------------------------
        */

        $set(
            'description',
            'Tarifario oficial ADN Publicidad. '
            .
            'Precio para ADN: L'
            .
            number_format(
                $cost,
                2,
                '.',
                ','
            )
            .
            '. Precio público: L'
            .
            number_format(
                $public,
                2,
                '.',
                ','
            )
            .
            '.'
        );

        /*
        |--------------------------------------------------------------------------
        | LIMPIAR CAMPOS DE PRECIOS
        |--------------------------------------------------------------------------
        |
        | Esto evita que al volver a ejecutar el seeder un producto AREA,
        | por ejemplo, conserve también accidentalmente un sale_price viejo.
        |
        */

        $set(
            'cost_price',
            null
        );

        $set(
            'sale_price',
            null
        );

        $set(
            'cost_rate',
            null
        );

        $set(
            'sale_rate',
            null
        );

        $set(
            'fixed_cost',
            null
        );

        $set(
            'fixed_price',
            null
        );

        /*
        |--------------------------------------------------------------------------
        | PRECIOS SEGÚN MÉTODO
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $method,
                [
                    'AREA',
                    'LINEAR',
                ],
                true
            )
        ) {
            $set(
                'cost_rate',
                $cost
            );

            $set(
                'sale_rate',
                $public
            );
        } else {
            $set(
                'cost_price',
                $cost
            );

            $set(
                'sale_price',
                $public
            );

            if (
                $method ===
                'FIXED'
            ) {
                $set(
                    'fixed_cost',
                    $cost
                );

                $set(
                    'fixed_price',
                    $public
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MARGEN
        |--------------------------------------------------------------------------
        |
        | Margen bruto:
        |
        | (precio público - costo ADN)
        | -------------------------------- × 100
        |          precio público
        |
        */

        $margin =
            $public > 0
                ? round(
                    (
                        (
                            $public
                            -
                            $cost
                        )
                        /
                        $public
                    )
                    *
                    100,
                    2
                )
                : 0;

        /*
        |--------------------------------------------------------------------------
        | MARKUP
        |--------------------------------------------------------------------------
        |
        | (precio público - costo ADN)
        | -------------------------------- × 100
        |             costo ADN
        |
        */

        $markup =
            $cost > 0
                ? round(
                    (
                        (
                            $public
                            -
                            $cost
                        )
                        /
                        $cost
                    )
                    *
                    100,
                    2
                )
                : 0;

        $set(
            'margin_percentage',
            $margin
        );

        $set(
            'markup_percentage',
            $markup
        );

        /*
        |--------------------------------------------------------------------------
        | COMPORTAMIENTO
        |--------------------------------------------------------------------------
        */

        $set(
            'active',
            true
        );

        $set(
            'tracks_inventory',
            false
        );

        $set(
            'requires_production',
            false
        );

        $set(
            'allows_installation',
            false
        );

        $set(
            'visible_on_website',
            false
        );

        $set(
            'sort_order',
            $sortOrder
        );

        if (
            $this->hasColumn(
                $columns,
                'deleted_at'
            )
        ) {
            $payload[
                'deleted_at'
            ] =
                null;
        }

        return $payload;
    }

    /**
     * @param array<int, string> $columns
     */
    private function hasColumn(
        array $columns,
        string $column
    ): bool {
        return in_array(
            $column,
            $columns,
            true
        );
    }

    /**
     * Lista oficial proporcionada por ADN Publicidad.
     *
     * cost   = precio para ADN.
     * public = precio público / cliente.
     *
     * @return array<int, array<string, mixed>>
     */
    private function priceList(): array
    {
        $rows = [
            [
                'REV-001',
                'Impresión Sticker',
                0.18,
                0.62,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-002',
                'Banner en lona',
                0.18,
                0.62,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-003',
                'Sticker sobre PVC 3 mm',
                0.38,
                0.65,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-004',
                'Sticker sobre PVC 5 mm',
                0.58,
                0.87,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-005',
                'Banner Araña',
                1180.00,
                1600.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-006',
                'Estructura Araña',
                450.00,
                570.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-007',
                'Banner Roll Up 31 x 81 pulg',
                1290.00,
                2000.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-008',
                'Tarjeta Opalina 1 cara',
                2.00,
                3.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-009',
                'Tarjeta Opalina 2 caras',
                4.00,
                5.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-010',
                'Lona traslúcida 4 pasadas',
                0.27,
                0.79,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-011',
                'Microperforado',
                0.32,
                0.77,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-012',
                'Sticker troquelado',
                0.23,
                0.67,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-013',
                'Sandblast troquelado',
                0.42,
                0.77,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-014',
                'Sticker con protección',
                0.57,
                0.99,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-015',
                'Corplast',
                0.57,
                0.87,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-016',
                'Tencard PVC 5 mm',
                0.52,
                0.87,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-017',
                'Camisa Polo - solo logo y nombre',
                250.00,
                350.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-018',
                'Camisa Pacer',
                200.00,
                300.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-019',
                'Impresión Camisa',
                90.00,
                150.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-020',
                'Lápiz tinta',
                55.00,
                90.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-021',
                'Taza blanca normal',
                75.00,
                100.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-022',
                'Taza térmica mágica',
                115.00,
                230.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-023',
                'Gorra lona',
                150.00,
                200.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-024',
                'Gorra esponja',
                90.00,
                150.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-025',
                'Carnet empresarial',
                150.00,
                250.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-026',
                'Camisa Polycotton',
                150.00,
                200.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-027',
                'Cartoncillo',
                0.18,
                0.38,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-028',
                'Cordón para carnet',
                80.00,
                150.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-029',
                'DTF A3',
                95.00,
                130.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-030',
                'DTF A4',
                50.00,
                80.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-031',
                'DTF metro lineal',
                195.00,
                230.00,
                'LINEAR',
                'METRO_LINEAL',
            ],
            [
                'REV-032',
                'Ojete',
                0.18,
                0.38,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-033',
                'Magnético',
                0.72,
                1.00,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-034',
                'Troquelado con protección',
                0.43,
                0.86,
                'AREA',
                'PULGADA2',
            ],
            [
                'REV-035',
                'Acrílico Pedagoga del Año',
                250.00,
                700.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-036',
                'Placas CEB Dr. Carlos',
                200.00,
                550.00,
                'UNIT',
                'UNIDAD',
            ],
            [
                'REV-037',
                'Yarda de transparente',
                90.00,
                150.00,
                'UNIT',
                'YARDA',
            ],
        ];

        return array_map(
            static fn (
                array $row
            ): array => [
                'code' =>
                    $row[0],

                'name' =>
                    $row[1],

                'cost' =>
                    $row[2],

                'public' =>
                    $row[3],

                'method' =>
                    $row[4],

                'unit' =>
                    $row[5],
            ],
            $rows
        );
    }
}