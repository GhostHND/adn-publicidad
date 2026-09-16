<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\WebsiteContent;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicWebsiteController extends Controller
{
    public function home(): Response
    {
        return Inertia::render(
            'Public/Home',
            [
                'site' =>
                    $this->siteData(),

                'services' =>
                    $this->contents(
                        'service',
                        6
                    ),

                'portfolio' =>
                    $this->contents(
                        'portfolio',
                        6
                    ),
            ]
        );
    }

    public function services(): Response
    {
        return Inertia::render(
            'Public/Services',
            [
                'site' =>
                    $this->siteData(),

                'services' =>
                    $this->contents(
                        'service'
                    ),
            ]
        );
    }

    public function portfolio(): Response
    {
        return Inertia::render(
            'Public/Portfolio',
            [
                'site' =>
                    $this->siteData(),

                'portfolio' =>
                    $this->contents(
                        'portfolio'
                    ),
            ]
        );
    }

    public function contact(): Response
    {
        return Inertia::render(
            'Public/Contact',
            [
                'site' =>
                    $this->siteData(),

                'services' =>
                    WebsiteContent::query()
                        ->active()
                        ->type(
                            'service'
                        )
                        ->orderBy(
                            'sort_order'
                        )
                        ->orderBy(
                            'id'
                        )
                        ->get([
                            'id',
                            'title',
                        ])
                        ->map(
                            fn (
                                WebsiteContent $service
                            ) => [
                                'id' =>
                                    $service->id,

                                'title' =>
                                    $service->title,
                            ]
                        )
                        ->values()
                        ->all(),
            ]
        );
    }

    private function contents(
        string $type,
        ?int $limit = null
    ): array {
        $query =
            WebsiteContent::query()
                ->active()
                ->type(
                    $type
                )
                ->orderBy(
                    'sort_order'
                )
                ->orderBy(
                    'id'
                );

        if ($limit !== null) {
            $query->limit(
                $limit
            );
        }

        return $query
            ->get()
            ->map(
                fn (
                    WebsiteContent $content
                ) => [
                    'id' =>
                        $content->id,

                    'content_type' =>
                        $content->content_type,

                    'title' =>
                        $content->title,

                    'subtitle' =>
                        $content->subtitle,

                    'description' =>
                        $content->description,

                    'image_url' =>
                        $content->image_path
                            ? Storage::disk(
                                'public'
                            )->url(
                                $content->image_path
                            )
                            : null,

                    'link_url' =>
                        $content->link_url,

                    'sort_order' =>
                        $content->sort_order,
                ]
            )
            ->values()
            ->all();
    }

    private function siteData(): array
    {
        $phone =
            (string)
            SystemSetting::value(
                'phone',
                config(
                    'adn.phone',
                    ''
                )
            );

        $phoneDigits =
            preg_replace(
                '/\D+/',
                '',
                $phone
            )
            ?? '';

        return [
            /*
            |--------------------------------------------------------------------------
            | IDENTIDAD / CONTACTO
            |--------------------------------------------------------------------------
            |
            | Estas claves son las mismas que utiliza Configuración del sistema.
            | Se mantiene una sola fuente de información.
            |
            */

            'business_name' =>
                SystemSetting::value(
                    'business_name',
                    config(
                        'adn.business_name',
                        'ADN Publicidad'
                    )
                ),

            'business_location' =>
                SystemSetting::value(
                    'business_location',
                    config(
                        'adn.business_location',
                        ''
                    )
                ),

            'phone' =>
                $phone,

            'phone_digits' =>
                $phoneDigits,

            'whatsapp_url' =>
                $phoneDigits !== ''
                    ? 'https://wa.me/' .
                        $phoneDigits
                    : null,

            'email' =>
                SystemSetting::value(
                    'email',
                    config(
                        'adn.email',
                        ''
                    )
                ),

            'website' =>
                SystemSetting::value(
                    'website',
                    config(
                        'adn.website',
                        ''
                    )
                ),

            'logo' =>
                $this->logoDataUri(),

            /*
            |--------------------------------------------------------------------------
            | PORTADA
            |--------------------------------------------------------------------------
            */

            'hero_title' =>
                SystemSetting::value(
                    'site.hero_title',
                    'Publicidad que hace visible tu negocio'
                ),

            'hero_subtitle' =>
                SystemSetting::value(
                    'site.hero_subtitle',
                    'Diseño, impresión, rotulación, seguridad y soluciones publicitarias para llevar tus ideas a otro nivel.'
                ),

            'hero_button_text' =>
                SystemSetting::value(
                    'site.hero_button_text',
                    'Solicitar cotización'
                ),

            /*
            |--------------------------------------------------------------------------
            | NOSOTROS
            |--------------------------------------------------------------------------
            */

            'about_title' =>
                SystemSetting::value(
                    'site.about_title',
                    'Soluciones hechas para destacar'
                ),

            'about_text' =>
                SystemSetting::value(
                    'site.about_text',
                    'En ADN Publicidad combinamos diseño, producción y tecnología para crear soluciones visuales y comerciales adaptadas a cada proyecto.'
                ),

            /*
            |--------------------------------------------------------------------------
            | PIE / REDES
            |--------------------------------------------------------------------------
            */

            'footer_text' =>
                SystemSetting::value(
                    'site.footer_text',
                    'Diseño, publicidad y tecnología en un solo lugar.'
                ),

            'facebook_url' =>
                SystemSetting::value(
                    'site.facebook_url',
                    ''
                ),

            'instagram_url' =>
                SystemSetting::value(
                    'site.instagram_url',
                    ''
                ),

            'tiktok_url' =>
                SystemSetting::value(
                    'site.tiktok_url',
                    ''
                ),

            /*
            |--------------------------------------------------------------------------
            | RUTAS PÚBLICAS
            |--------------------------------------------------------------------------
            */

            'home_url' =>
                '/',

            'services_url' =>
                '/servicios',

            'portfolio_url' =>
                '/portafolio',

            'contact_url' =>
                '/contacto',
        ];
    }

    private function logoDataUri(): ?string
    {
        $path =
            SystemSetting::value(
                'logo_path',
                config(
                    'adn.logo_path',
                    'src/logo_adn.svg'
                )
            );

        if (!$path) {
            return null;
        }

        if (
            preg_match(
                '~^https?://~i',
                $path
            )
        ) {
            return $path;
        }

        $fullPath =
            base_path(
                ltrim(
                    $path,
                    '/\\'
                )
            );

        if (
            !is_file(
                $fullPath
            )
        ) {
            return null;
        }

        $extension =
            strtolower(
                pathinfo(
                    $fullPath,
                    PATHINFO_EXTENSION
                )
            );

        $mime =
            match (
                $extension
            ) {
                'svg' =>
                    'image/svg+xml',

                'png' =>
                    'image/png',

                'jpg',
                'jpeg' =>
                    'image/jpeg',

                'webp' =>
                    'image/webp',

                default =>
                    'application/octet-stream',
            };

        $contents =
            file_get_contents(
                $fullPath
            );

        if (
            $contents ===
            false
        ) {
            return null;
        }

        return
            'data:' .
            $mime .
            ';base64,' .
            base64_encode(
                $contents
            );
    }
}