<?php

namespace App\Providers;

use App\Http\Controllers\BusinessDocumentController;
use App\Http\Controllers\DashboardMetricsController;
use App\Http\Controllers\DocumentShareController;
use App\Http\Controllers\Integration\AdnWebLeadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\WebsiteLeadController;
use App\Http\Controllers\WebsiteLeadDetailController;
use App\Http\Middleware\VerifyAdnWebSignature;
use App\Models\CctvProject;
use App\Models\Installation;
use App\Models\InventoryItem;
use App\Models\Quotation;
use App\Models\SalePayment;
use App\Models\SystemSetting;
use App\Models\WebsiteLead;
use App\Models\WorkOrderItem;
use App\Observers\InstallationObserver;
use App\Observers\InventoryItemObserver;
use App\Observers\QuotationDocumentObserver;
use App\Observers\QuotationLeadObserver;
use App\Observers\QuotationObserver;
use App\Observers\SalePaymentDocumentObserver;
use App\Observers\WorkOrderItemObserver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->applySystemSettings();

        Route::model(
            'cctvJob',
            CctvProject::class
        );

        Quotation::observe(
            QuotationObserver::class
        );

        Quotation::observe(
            QuotationDocumentObserver::class
        );

        Quotation::observe(
            QuotationLeadObserver::class
        );

        InventoryItem::observe(
            InventoryItemObserver::class
        );

        WorkOrderItem::observe(
            WorkOrderItemObserver::class
        );

        Installation::observe(
            InstallationObserver::class
        );

        SalePayment::observe(
            SalePaymentDocumentObserver::class
        );

        /*
        |--------------------------------------------------------------------------
        | ESTADO DE SOLICITUDES
        |--------------------------------------------------------------------------
        */

        Inertia::share(
            'leadState',
            function (): array {
                try {
                    if (
                        !auth()->check()
                        ||
                        !Schema::hasTable(
                            'website_leads'
                        )
                    ) {
                        return [
                            'new_count' =>
                                0,

                            'latest_id' =>
                                0,
                        ];
                    }

                    return [
                        'new_count' =>
                            WebsiteLead::query()
                                ->unreviewed()
                                ->count(),

                        'latest_id' =>
                            WebsiteLead::query()
                                ->max(
                                    'id'
                                )
                            ?? 0,
                    ];
                } catch (
                    Throwable
                ) {
                    return [
                        'new_count' =>
                            0,

                        'latest_id' =>
                            0,
                    ];
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | URL DEL EDITOR ADN WEB
        |--------------------------------------------------------------------------
        |
        | No exponemos ninguna clave ni secreto.
        |
        | LOCAL:
        | ADN_WEB_EDITOR_URL=http://127.0.0.1:8001
        | Resultado:
        | http://127.0.0.1:8001/editor-preview
        |
        | PRODUCCIÓN:
        | ADN_WEB_EDITOR_URL=https://editor.adnpublicidad.site
        | Resultado:
        | https://editor.adnpublicidad.site
        |
        */

        Inertia::share(
            'adnWebEditorUrl',
            function (): ?string {
                $editorUrl =
                    rtrim(
                        trim(
                            (string) config(
                                'adn_integration.editor_url',
                                ''
                            )
                        ),
                        '/'
                    );

                if (
                    $editorUrl ===
                    ''
                ) {
                    return null;
                }

                if (
                    app()->environment(
                        'local'
                    )
                    &&
                    !str_ends_with(
                        $editorUrl,
                        '/editor-preview'
                    )
                ) {
                    $editorUrl .=
                        '/editor-preview';
                }

                return $editorUrl;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | INTEGRACIÓN ADN WEB -> ADN APP
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            VerifyAdnWebSignature::class,
            'throttle:30,1',
        ])
            ->post(
                '/integrations/adn-web/leads',
                [
                    AdnWebLeadController::class,
                    'store',
                ]
            )
            ->name(
                'integrations.adn-web.leads'
            );

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/dashboard/metrics',
                [
                    DashboardMetricsController::class,
                    'index',
                ]
            )
            ->name(
                'dashboard.metrics'
            );

        /*
        |--------------------------------------------------------------------------
        | SOLICITUD PÚBLICA
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'throttle:10,1',
        ])
            ->post(
                '/contacto/solicitud',
                [
                    WebsiteLeadController::class,
                    'store',
                ]
            )
            ->name(
                'public.leads.store'
            );

        /*
        |--------------------------------------------------------------------------
        | SOLICITUDES
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])->group(
            function (): void {
                Route::get(
                    '/website/leads',
                    [
                        WebsiteLeadController::class,
                        'index',
                    ]
                )
                    ->name(
                        'website.leads.index'
                    );

                /*
                |--------------------------------------------------------------------------
                | DETALLE COMPLETO
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/website/leads/{websiteLead}/detail',
                    [
                        WebsiteLeadDetailController::class,
                        'show',
                    ]
                )
                    ->name(
                        'website.leads.detail'
                    );

                Route::get(
                    '/website/leads/{websiteLead}/files/{fileId}/view',
                    [
                        WebsiteLeadDetailController::class,
                        'previewFile',
                    ]
                )
                    ->whereNumber(
                        'fileId'
                    )
                    ->name(
                        'website.leads.files.preview'
                    );

                Route::get(
                    '/website/leads/{websiteLead}/files/{fileId}/download',
                    [
                        WebsiteLeadDetailController::class,
                        'downloadFile',
                    ]
                )
                    ->whereNumber(
                        'fileId'
                    )
                    ->name(
                        'website.leads.files.download'
                    );

                Route::patch(
                    '/website/leads/{websiteLead}/reviewed',
                    [
                        WebsiteLeadController::class,
                        'markReviewed',
                    ]
                )
                    ->name(
                        'website.leads.reviewed'
                    );

                Route::post(
                    '/website/leads/{websiteLead}/attend',
                    [
                        WebsiteLeadController::class,
                        'attend',
                    ]
                )
                    ->name(
                        'website.leads.attend'
                    );

                Route::patch(
                    '/website/leads/{websiteLead}/status',
                    [
                        WebsiteLeadController::class,
                        'updateStatus',
                    ]
                )
                    ->name(
                        'website.leads.status'
                    );

                Route::delete(
                    '/website/leads/{websiteLead}',
                    [
                        WebsiteLeadController::class,
                        'destroy',
                    ]
                )
                    ->name(
                        'website.leads.destroy'
                    );

                Route::get(
                    '/notifications/leads',
                    [
                        NotificationController::class,
                        'leadFeed',
                    ]
                )
                    ->name(
                        'notifications.leads'
                    );

                Route::get(
                    '/notifications/leads/{websiteLead}',
                    [
                        NotificationController::class,
                        'lead',
                    ]
                )
                    ->name(
                        'notifications.leads.show'
                    );

                Route::get(
                    '/notifications/push/config',
                    [
                        NotificationController::class,
                        'pushConfig',
                    ]
                )
                    ->name(
                        'notifications.push.config'
                    );

                Route::post(
                    '/notifications/push/subscribe',
                    [
                        NotificationController::class,
                        'subscribe',
                    ]
                )
                    ->name(
                        'notifications.push.subscribe'
                    );

                Route::delete(
                    '/notifications/push/unsubscribe',
                    [
                        NotificationController::class,
                        'unsubscribe',
                    ]
                )
                    ->name(
                        'notifications.push.unsubscribe'
                    );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | REPORTES
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/reports',
                [
                    ReportsController::class,
                    'index',
                ]
            )
            ->name(
                'reports.index'
            );

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/quotations/{quotation}/pdf',
                [
                    BusinessDocumentController::class,
                    'quotation',
                ]
            )
            ->name(
                'quotations.pdf'
            );

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/receipts/{payment}/pdf',
                [
                    BusinessDocumentController::class,
                    'receipt',
                ]
            )
            ->name(
                'receipts.pdf'
            );

        /*
        |--------------------------------------------------------------------------
        | COMPARTIR
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/quotations/{quotation}/share',
                [
                    DocumentShareController::class,
                    'quotation',
                ]
            )
            ->name(
                'quotations.share'
            );

        Route::middleware([
            'web',
            'auth',
            'verified',
        ])
            ->get(
                '/receipts/{payment}/share',
                [
                    DocumentShareController::class,
                    'receipt',
                ]
            )
            ->name(
                'receipts.share'
            );
    }

    private function applySystemSettings(): void
    {
        try {
            if (
                !Schema::hasTable(
                    'system_settings'
                )
            ) {
                return;
            }

            $settings =
                SystemSetting::query()
                    ->pluck(
                        'value',
                        'key'
                    );

            foreach (
                $settings
                as $key => $value
            ) {
                Config::set(
                    'adn.'
                    . $key,
                    $value
                );
            }

            $timezone =
                $settings->get(
                    'timezone'
                );

            if (
                $timezone
            ) {
                Config::set(
                    'app.timezone',
                    $timezone
                );

                if (
                    in_array(
                        $timezone,
                        timezone_identifiers_list(),
                        true
                    )
                ) {
                    date_default_timezone_set(
                        $timezone
                    );
                }
            }
        } catch (
            Throwable
        ) {
            return;
        }
    }
}