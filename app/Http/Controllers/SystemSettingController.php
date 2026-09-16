<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingController extends Controller
{
    public function index(): Response
    {
        $settings =
            SystemSetting::query()
                ->orderBy(
                    'group_name'
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                )
                ->get()
                ->map(
                    fn (
                        SystemSetting $setting
                    ) => [
                        'id' =>
                            $setting->id,

                        'key' =>
                            $setting->key,

                        'group_name' =>
                            $setting->group_name,

                        'label' =>
                            $setting->label,

                        'value' =>
                            $setting->value
                            ?? '',

                        'type' =>
                            $setting->type,

                        'description' =>
                            $setting->description,

                        'sort_order' =>
                            $setting->sort_order,
                    ]
                )
                ->values();

        return Inertia::render(
            'SystemSettings/Index',
            [
                'settings' =>
                    $settings,
            ]
        );
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'items' => [
                    'required',
                    'array',
                ],

                'items.*.key' => [
                    'required',
                    'string',

                    Rule::exists(
                        'system_settings',
                        'key'
                    ),
                ],

                'items.*.value' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ]);

        DB::transaction(
            function () use (
                $validated
            ) {
                foreach (
                    $validated['items']
                    as $item
                ) {
                    $value =
                        $this
                            ->normalizeValue(
                                $item['key'],
                                $item['value']
                                    ?? null
                            );

                    SystemSetting::query()
                        ->where(
                            'key',
                            $item['key']
                        )
                        ->update([
                            'value' =>
                                $value,

                            'updated_at' =>
                                now(),
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            'Configuración actualizada correctamente.'
        );
    }

    private function normalizeValue(
        string $key,
        ?string $value
    ): ?string {
        $value =
            $value !== null
                ? trim(
                    $value
                )
                : null;

        /*
        |--------------------------------------------------------------------------
        | NOMBRE DEL NEGOCIO
        |--------------------------------------------------------------------------
        */

        if (
            $key ===
            'business_name'
            &&
            !$value
        ) {
            return 'ADN Publicidad';
        }

        /*
        |--------------------------------------------------------------------------
        | MONEDA
        |--------------------------------------------------------------------------
        */

        if (
            $key ===
            'currency_symbol'
            &&
            !$value
        ) {
            return 'L';
        }

        /*
        |--------------------------------------------------------------------------
        | ZONA HORARIA
        |--------------------------------------------------------------------------
        */

        if (
            $key ===
            'timezone'
            &&
            !$value
        ) {
            return 'America/Tegucigalpa';
        }

        /*
        |--------------------------------------------------------------------------
        | SITIO WEB
        |--------------------------------------------------------------------------
        |
        | Permitimos escribir:
        |
        | adnpublicidad.site
        | www.adnpublicidad.site
        | https://adnpublicidad.site
        | https://www.adnpublicidad.site
        |
        | Si no incluye protocolo se agrega https:// automáticamente.
        |
        */

        if (
            $key ===
            'website'
        ) {
            if (!$value) {
                return null;
            }

            if (
                !preg_match(
                    '~^https?://~i',
                    $value
                )
            ) {
                $value =
                    'https://' .
                    $value;
            }

            return $value;
        }

        /*
        |--------------------------------------------------------------------------
        | CORREO
        |--------------------------------------------------------------------------
        */

        if (
            $key ===
            'email'
            &&
            $value
        ) {
            return strtolower(
                $value
            );
        }

        return $value;
    }
}