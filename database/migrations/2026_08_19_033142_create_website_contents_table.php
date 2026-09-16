<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_contents', function (Blueprint $table) {
            $table->id();

            $table->string('content_type', 50)
                ->index();

            $table->string('title', 200);

            $table->string('subtitle', 255)
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->string('image_path', 500)
                ->nullable();

            $table->string('link_url', 500)
                ->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->boolean('active')
                ->default(true)
                ->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'content_type',
                'active',
                'sort_order',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | SERVICIOS INICIALES DE ADN PUBLICIDAD
        |--------------------------------------------------------------------------
        */

        $now = now();

        DB::table('website_contents')->insert([
            [
                'content_type' => 'service',
                'title' => 'Diseño gráfico',
                'subtitle' => 'Creatividad que comunica',
                'description' =>
                    'Diseñamos artes publicitarios, identidad visual y piezas gráficas adaptadas a las necesidades de cada cliente.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 10,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'content_type' => 'service',
                'title' => 'Banners y lonas',
                'subtitle' => 'Publicidad de gran formato',
                'description' =>
                    'Producción de banners y lonas publicitarias para negocios, eventos, promociones y campañas.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 20,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'content_type' => 'service',
                'title' => 'Rótulos publicitarios',
                'subtitle' => 'Haz visible tu negocio',
                'description' =>
                    'Diseño y fabricación de rótulos, PVC, soluciones iluminadas y proyectos publicitarios personalizados.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 30,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'content_type' => 'service',
                'title' => 'Cámaras de seguridad',
                'subtitle' => 'Protección y tecnología',
                'description' =>
                    'Instalación y configuración de sistemas CCTV para hogares, comercios y empresas.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 40,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'content_type' => 'service',
                'title' => 'Sublimación y estampados',
                'subtitle' => 'Personalización profesional',
                'description' =>
                    'Personalizamos productos y prendas para empresas, eventos, equipos y proyectos especiales.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 50,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            [
                'content_type' => 'service',
                'title' => 'Impresión y stickers',
                'subtitle' => 'Producción personalizada',
                'description' =>
                    'Impresiones, adhesivos, stickers y soluciones gráficas adaptadas al tamaño y acabado que necesitas.',
                'image_path' => null,
                'link_url' => null,
                'sort_order' => 60,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'website_contents'
        );
    }
};