<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\WebsiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function index(): Response
    {
        $definitions =
            $this->settingDefinitions();

        $storedValues =
            SystemSetting::query()
                ->whereIn(
                    'key',
                    array_keys(
                        $definitions
                    )
                )
                ->pluck(
                    'value',
                    'key'
                );

        $settings =
            collect(
                $definitions
            )
                ->map(
                    function (
                        array $definition,
                        string $key
                    ) use (
                        $storedValues
                    ) {
                        return [
                            'key' =>
                                $key,

                            'label' =>
                                $definition['label'],

                            'value' =>
                                $storedValues->has(
                                    $key
                                )
                                    ? (
                                        $storedValues[
                                            $key
                                        ]
                                        ?? ''
                                    )
                                    : $definition[
                                        'default'
                                    ],

                            'type' =>
                                $definition['type'],

                            'description' =>
                                $definition[
                                    'description'
                                ],

                            'group' =>
                                $definition[
                                    'group'
                                ],
                        ];
                    }
                )
                ->values()
                ->all();

        $contents =
            WebsiteContent::query()
                ->orderBy(
                    'content_type'
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
                        WebsiteContent $content
                    ) => $this
                        ->contentForFrontend(
                            $content
                        )
                )
                ->values()
                ->all();

        $publicUrl =
            (string)
            SystemSetting::value(
                'website',
                config(
                    'adn.website',
                    'https://www.adnpublicidad.site'
                )
            );

        return Inertia::render(
            'Website/Index',
            [
                'settings' =>
                    $settings,

                'contents' =>
                    $contents,

                'public_url' =>
                    $publicUrl,

                'stats' => [
                    'total' =>
                        count(
                            $contents
                        ),

                    'active' =>
                        collect(
                            $contents
                        )
                            ->where(
                                'active',
                                true
                            )
                            ->count(),

                    'services' =>
                        collect(
                            $contents
                        )
                            ->where(
                                'content_type',
                                'service'
                            )
                            ->count(),

                    'portfolio' =>
                        collect(
                            $contents
                        )
                            ->where(
                                'content_type',
                                'portfolio'
                            )
                            ->count(),
                ],
            ]
        );
    }

    public function updateSettings(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'settings' => [
                    'required',
                    'array',
                ],

                'settings.*' => [
                    'nullable',
                    'string',
                    'max:10000',
                ],
            ]);

        $definitions =
            $this->settingDefinitions();

        DB::transaction(
            function () use (
                $validated,
                $definitions
            ) {
                foreach (
                    $definitions
                    as $key => $definition
                ) {
                    if (
                        !array_key_exists(
                            $key,
                            $validated[
                                'settings'
                            ]
                        )
                    ) {
                        continue;
                    }

                    $value =
                        trim(
                            (string)
                            (
                                $validated[
                                    'settings'
                                ][$key]
                                ?? ''
                            )
                        );

                    SystemSetting::query()
                        ->updateOrCreate(
                            [
                                'key' =>
                                    $key,
                            ],
                            [
                                'group_name' =>
                                    'Sitio web',

                                'label' =>
                                    $definition[
                                        'label'
                                    ],

                                'value' =>
                                    $value,

                                'type' =>
                                    $definition[
                                        'type'
                                    ],

                                'description' =>
                                    $definition[
                                        'description'
                                    ],

                                'sort_order' =>
                                    $definition[
                                        'sort_order'
                                    ],
                            ]
                        );
                }
            }
        );

        return back()->with(
            'success',
            'Configuración del sitio web actualizada correctamente.'
        );
    }

    public function storeContent(
        Request $request
    ): RedirectResponse {
        $validated =
            $this->validateContent(
                $request
            );

        $imagePath =
            $request->hasFile(
                'image'
            )
                ? $request
                    ->file(
                        'image'
                    )
                    ->store(
                        'website',
                        'public'
                    )
                : null;

        WebsiteContent::query()
            ->create([
                'content_type' =>
                    $validated[
                        'content_type'
                    ],

                'title' =>
                    $validated[
                        'title'
                    ],

                'subtitle' =>
                    $validated[
                        'subtitle'
                    ]
                    ?? null,

                'description' =>
                    $validated[
                        'description'
                    ]
                    ?? null,

                'image_path' =>
                    $imagePath,

                'link_url' =>
                    $validated[
                        'link_url'
                    ]
                    ?? null,

                'sort_order' =>
                    $validated[
                        'sort_order'
                    ]
                    ?? 0,

                'active' =>
                    (bool)
                    (
                        $validated[
                            'active'
                        ]
                        ?? true
                    ),
            ]);

        return back()->with(
            'success',
            'Contenido creado correctamente.'
        );
    }

    public function updateContent(
        Request $request,
        WebsiteContent $content
    ): RedirectResponse {
        $validated =
            $this->validateContent(
                $request
            );

        $imagePath =
            $content->image_path;

        if (
            (
                $validated[
                    'remove_image'
                ]
                ?? false
            )
            &&
            $imagePath
        ) {
            Storage::disk(
                'public'
            )->delete(
                $imagePath
            );

            $imagePath =
                null;
        }

        if (
            $request->hasFile(
                'image'
            )
        ) {
            if ($imagePath) {
                Storage::disk(
                    'public'
                )->delete(
                    $imagePath
                );
            }

            $imagePath =
                $request
                    ->file(
                        'image'
                    )
                    ->store(
                        'website',
                        'public'
                    );
        }

        $content->update([
            'content_type' =>
                $validated[
                    'content_type'
                ],

            'title' =>
                $validated[
                    'title'
                ],

            'subtitle' =>
                $validated[
                    'subtitle'
                ]
                ?? null,

            'description' =>
                $validated[
                    'description'
                ]
                ?? null,

            'image_path' =>
                $imagePath,

            'link_url' =>
                $validated[
                    'link_url'
                ]
                ?? null,

            'sort_order' =>
                $validated[
                    'sort_order'
                ]
                ?? 0,

            'active' =>
                (bool)
                (
                    $validated[
                        'active'
                    ]
                    ?? false
                ),
        ]);

        return back()->with(
            'success',
            'Contenido actualizado correctamente.'
        );
    }

    public function destroyContent(
        WebsiteContent $content
    ): RedirectResponse {
        DB::transaction(
            function () use (
                $content
            ) {
                if (
                    $content->image_path
                ) {
                    Storage::disk(
                        'public'
                    )->delete(
                        $content
                            ->image_path
                    );
                }

                $content->delete();
            }
        );

        return back()->with(
            'success',
            'Contenido eliminado correctamente.'
        );
    }

    private function validateContent(
        Request $request
    ): array {
        return $request->validate([
            'content_type' => [
                'required',

                Rule::in([
                    'service',
                    'portfolio',
                ]),
            ],

            'title' => [
                'required',
                'string',
                'max:200',
            ],

            'subtitle' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'link_url' => [
                'nullable',
                'string',
                'max:500',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],

            'active' => [
                'nullable',
                'boolean',
            ],

            'remove_image' => [
                'nullable',
                'boolean',
            ],
        ]);
    }

    private function contentForFrontend(
        WebsiteContent $content
    ): array {
        return [
            'id' =>
                $content->id,

            'content_type' =>
                $content
                    ->content_type,

            'title' =>
                $content->title,

            'subtitle' =>
                $content->subtitle,

            'description' =>
                $content
                    ->description,

            'image_path' =>
                $content
                    ->image_path,

            'image_url' =>
                $content
                    ->image_path
                    ? Storage::disk(
                        'public'
                    )->url(
                        $content
                            ->image_path
                    )
                    : null,

            'link_url' =>
                $content
                    ->link_url,

            'sort_order' =>
                (int)
                $content
                    ->sort_order,

            'active' =>
                (bool)
                $content
                    ->active,
        ];
    }

    private function settingDefinitions(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | NEGOCIO
            |--------------------------------------------------------------------------
            |
            | Son las mismas claves globales del sistema.
            | Editarlas aquí actualiza la misma información usada por el resto
            | de ADN Publicidad; no se duplica información.
            |
            */

            'business_name' => [
                'label' =>
                    'Nombre comercial',

                'default' =>
                    config(
                        'adn.business_name',
                        'ADN Publicidad'
                    ),

                'type' =>
                    'text',

                'description' =>
                    'Nombre mostrado en el sitio público.',

                'group' =>
                    'business',

                'sort_order' =>
                    10,
            ],

            'business_location' => [
                'label' =>
                    'Ubicación',

                'default' =>
                    config(
                        'adn.business_location',
                        ''
                    ),

                'type' =>
                    'text',

                'description' =>
                    'Ciudad o ubicación comercial mostrada al cliente.',

                'group' =>
                    'business',

                'sort_order' =>
                    20,
            ],

            'phone' => [
                'label' =>
                    'Teléfono / WhatsApp',

                'default' =>
                    config(
                        'adn.phone',
                        ''
                    ),

                'type' =>
                    'text',

                'description' =>
                    'Número principal de contacto del negocio.',

                'group' =>
                    'business',

                'sort_order' =>
                    30,
            ],

            'email' => [
                'label' =>
                    'Correo electrónico',

                'default' =>
                    config(
                        'adn.email',
                        ''
                    ),

                'type' =>
                    'text',

                'description' =>
                    'Correo de contacto mostrado en el sitio.',

                'group' =>
                    'business',

                'sort_order' =>
                    40,
            ],

            'website' => [
                'label' =>
                    'Dirección del sitio web',

                'default' =>
                    config(
                        'adn.website',
                        'https://www.adnpublicidad.site'
                    ),

                'type' =>
                    'text',

                'description' =>
                    'URL pública principal de ADN Publicidad.',

                'group' =>
                    'business',

                'sort_order' =>
                    50,
            ],

            /*
            |--------------------------------------------------------------------------
            | PORTADA / NOSOTROS
            |--------------------------------------------------------------------------
            */

            'site.hero_title' => [
                'label' =>
                    'Título principal',

                'default' =>
                    'Publicidad que hace visible tu negocio',

                'type' =>
                    'text',

                'description' =>
                    'Texto principal que aparece en la portada.',

                'group' =>
                    'home',

                'sort_order' =>
                    100,
            ],

            'site.hero_subtitle' => [
                'label' =>
                    'Descripción principal',

                'default' =>
                    'Diseño, impresión, rotulación, seguridad y soluciones publicitarias para llevar tus ideas a otro nivel.',

                'type' =>
                    'textarea',

                'description' =>
                    'Texto introductorio de la página principal.',

                'group' =>
                    'home',

                'sort_order' =>
                    110,
            ],

            'site.hero_button_text' => [
                'label' =>
                    'Texto del botón principal',

                'default' =>
                    'Solicitar cotización',

                'type' =>
                    'text',

                'description' =>
                    'Texto mostrado en el llamado a la acción principal.',

                'group' =>
                    'home',

                'sort_order' =>
                    120,
            ],

            'site.about_title' => [
                'label' =>
                    'Título de nosotros',

                'default' =>
                    'Soluciones hechas para destacar',

                'type' =>
                    'text',

                'description' =>
                    'Título de la sección acerca de ADN Publicidad.',

                'group' =>
                    'home',

                'sort_order' =>
                    130,
            ],

            'site.about_text' => [
                'label' =>
                    'Descripción de nosotros',

                'default' =>
                    'En ADN Publicidad combinamos diseño, producción y tecnología para crear soluciones visuales y comerciales adaptadas a cada proyecto.',

                'type' =>
                    'textarea',

                'description' =>
                    'Descripción institucional utilizada en el sitio.',

                'group' =>
                    'home',

                'sort_order' =>
                    140,
            ],

            'site.footer_text' => [
                'label' =>
                    'Texto del pie de página',

                'default' =>
                    'Diseño, publicidad y tecnología en un solo lugar.',

                'type' =>
                    'text',

                'description' =>
                    'Texto mostrado junto al nombre de ADN Publicidad en el pie de página.',

                'group' =>
                    'home',

                'sort_order' =>
                    150,
            ],

            /*
            |--------------------------------------------------------------------------
            | REDES
            |--------------------------------------------------------------------------
            */

            'site.facebook_url' => [
                'label' =>
                    'Facebook',

                'default' =>
                    '',

                'type' =>
                    'text',

                'description' =>
                    'Enlace completo al perfil o página de Facebook.',

                'group' =>
                    'social',

                'sort_order' =>
                    200,
            ],

            'site.instagram_url' => [
                'label' =>
                    'Instagram',

                'default' =>
                    '',

                'type' =>
                    'text',

                'description' =>
                    'Enlace completo al perfil de Instagram.',

                'group' =>
                    'social',

                'sort_order' =>
                    210,
            ],

            'site.tiktok_url' => [
                'label' =>
                    'TikTok',

                'default' =>
                    '',

                'type' =>
                    'text',

                'description' =>
                    'Enlace completo al perfil de TikTok.',

                'group' =>
                    'social',

                'sort_order' =>
                    220,
            ],
        ];
    }
}