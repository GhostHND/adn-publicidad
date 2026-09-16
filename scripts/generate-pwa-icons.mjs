import sharp from 'sharp';
import path from 'node:path';
import process from 'node:process';
import fs from 'node:fs/promises';

const ROOT =
    process.cwd();

const SOURCE =
    path.join(
        ROOT,
        'public',
        'icons',
        'logo_adn.svg',
    );

const OUTPUT =
    path.join(
        ROOT,
        'public',
        'icons',
    );

const BACKGROUND =
    '#061115';

await fs.mkdir(
    OUTPUT,
    {
        recursive: true,
    },
);

const createIcon =
    async ({
        size,
        scale,
        output,
    }) => {
        const logoSize =
            Math.round(
                size * scale,
            );

        const logo =
            await sharp(
                SOURCE,
            )
                .resize({
                    width:
                        logoSize,

                    height:
                        logoSize,

                    fit:
                        'contain',

                    withoutEnlargement:
                        false,
                })
                .png()
                .toBuffer();

        await sharp({
            create: {
                width:
                    size,

                height:
                    size,

                channels:
                    4,

                background:
                    BACKGROUND,
            },
        })
            .composite([
                {
                    input:
                        logo,

                    gravity:
                        'centre',
                },
            ])
            .png({
                compressionLevel:
                    9,

                adaptiveFiltering:
                    true,
            })
            .toFile(
                path.join(
                    OUTPUT,
                    output,
                ),
            );

        console.log(
            `✓ ${output}`,
        );
    };

/*
|--------------------------------------------------------------------------
| ICONO 192
|--------------------------------------------------------------------------
*/

await createIcon({
    size:
        192,

    scale:
        0.72,

    output:
        'pwa-192.png',
});

/*
|--------------------------------------------------------------------------
| ICONO 512
|--------------------------------------------------------------------------
*/

await createIcon({
    size:
        512,

    scale:
        0.72,

    output:
        'pwa-512.png',
});

/*
|--------------------------------------------------------------------------
| MASKABLE
|--------------------------------------------------------------------------
|
| El logo se mantiene más centrado y con mayor zona de seguridad.
|
*/

await createIcon({
    size:
        512,

    scale:
        0.60,

    output:
        'pwa-maskable-512.png',
});

/*
|--------------------------------------------------------------------------
| APPLE / IOS
|--------------------------------------------------------------------------
*/

await createIcon({
    size:
        180,

    scale:
        0.72,

    output:
        'apple-touch-icon.png',
});

console.log('');
console.log(
    'Iconos PWA de ADN Publicidad generados correctamente.',
);