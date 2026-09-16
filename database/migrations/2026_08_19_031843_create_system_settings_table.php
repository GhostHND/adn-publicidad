<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 150)
                ->unique();

            $table->string('group_name', 100)
                ->index();

            $table->string('label', 150);

            $table->text('value')
                ->nullable();

            $table->string('type', 30)
                ->default('text');

            $table->text('description')
                ->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CONFIGURACIÓN INICIAL
        |--------------------------------------------------------------------------
        |
        | Tomamos los valores que ADN Publicidad ya utiliza actualmente en
        | config/adn.php para no perder ninguna configuración existente.
        |
        */

        $now = now();

        $settings = [
            [
                'key' => 'business_name',
                'group_name' => 'Empresa',
                'label' => 'Nombre comercial',
                'value' => config(
                    'adn.business_name',
                    'ADN Publicidad'
                ),
                'type' => 'text',
                'description' =>
                    'Nombre que aparecerá en documentos y comunicaciones.',
                'sort_order' => 10,
            ],

            [
                'key' => 'business_location',
                'group_name' => 'Empresa',
                'label' => 'Ubicación',
                'value' => config(
                    'adn.business_location',
                    ''
                ),
                'type' => 'text',
                'description' =>
                    'Ciudad, departamento o ubicación general del negocio.',
                'sort_order' => 20,
            ],

            [
                'key' => 'phone',
                'group_name' => 'Contacto',
                'label' => 'Teléfono / WhatsApp',
                'value' => config(
                    'adn.phone',
                    ''
                ),
                'type' => 'text',
                'description' =>
                    'Número principal utilizado por ADN Publicidad.',
                'sort_order' => 10,
            ],

            [
                'key' => 'email',
                'group_name' => 'Contacto',
                'label' => 'Correo electrónico',
                'value' => config(
                    'adn.email',
                    ''
                ),
                'type' => 'email',
                'description' =>
                    'Correo comercial o administrativo.',
                'sort_order' => 20,
            ],

            [
                'key' => 'website',
                'group_name' => 'Contacto',
                'label' => 'Sitio web',
                'value' => config(
                    'adn.website',
                    ''
                ),
                'type' => 'url',
                'description' =>
                    'Dirección pública del sitio web.',
                'sort_order' => 30,
            ],

            [
                'key' => 'logo_path',
                'group_name' => 'Documentos',
                'label' => 'Ruta del logotipo',
                'value' => config(
                    'adn.logo_path',
                    ''
                ),
                'type' => 'text',
                'description' =>
                    'Ruta del logotipo utilizado en documentos PDF.',
                'sort_order' => 10,
            ],

            [
                'key' => 'currency_symbol',
                'group_name' => 'Documentos',
                'label' => 'Símbolo de moneda',
                'value' => config(
                    'adn.currency_symbol',
                    'L'
                ),
                'type' => 'text',
                'description' =>
                    'Símbolo mostrado en cotizaciones, recibos y documentos.',
                'sort_order' => 20,
            ],

            [
                'key' => 'share.quotation_message',
                'group_name' => 'Compartir documentos',
                'label' => 'Mensaje de cotización',
                'value' => config(
                    'adn.share.quotation_message',
                    ''
                ),
                'type' => 'textarea',
                'description' =>
                    'Mensaje utilizado cuando se comparte una cotización.',
                'sort_order' => 10,
            ],

            [
                'key' => 'share.receipt_message',
                'group_name' => 'Compartir documentos',
                'label' => 'Mensaje de recibo',
                'value' => config(
                    'adn.share.receipt_message',
                    ''
                ),
                'type' => 'textarea',
                'description' =>
                    'Mensaje utilizado cuando se comparte un recibo.',
                'sort_order' => 20,
            ],

            [
                'key' => 'timezone',
                'group_name' => 'Sistema',
                'label' => 'Zona horaria',
                'value' => config(
                    'app.timezone',
                    'America/Tegucigalpa'
                ),
                'type' => 'text',
                'description' =>
                    'Zona horaria utilizada por el sistema.',
                'sort_order' => 10,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert([
                ...$setting,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'system_settings'
        );
    }
};