<?php
use Bvtterfly\Lio\Optimizers\Jpegoptim;
use Bvtterfly\Lio\Optimizers\Pngquant;
use Bvtterfly\Lio\Optimizers\Optipng;
use Bvtterfly\Lio\Optimizers\Svgo;
use Bvtterfly\Lio\Optimizers\Gifsicle;
use Bvtterfly\Lio\Optimizers\Cwebp;

return [
    /*
     * If set to `default` it uses your default filesystem disk.
     * You can set it to any filesystem disks configured in your application.
     */
    'disk' => 'default',

    /*
     * If set to `true` all output of the optimizer binaries will be appended to the default log channel.
     * You can also set this to a class that implements `Psr\Log\LoggerInterface`
     * or any log channels you configured in your application.
     */
    'log_optimizer_activity' => false,

    /*
     * Optimizers are responsible for optimizing your image
     */
    'optimizers' => [
        Jpegoptim::class => [
            '--max=85',
            '--strip-all',
            '--all-progressive',
        ],
        Pngquant::class => [
            '--quality=85',
            '--force',
            '--skip-if-larger',
        ],
        Optipng::class => [
            '-i0',
            '-o2',
            '-quiet',
        ],
        Svgo::class => [
            '--disable={cleanupIDs,removeViewBox}',
        ],
        Gifsicle::class => [
            '-b',
            '-O3',
        ],
        Cwebp::class => [
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 80',
        ]
    ],

    /*
    * The maximum time in seconds each optimizer is allowed to run separately.
    */
    'timeout' => 60,

    /*
    * The directory where your binaries are stored.
    * Only use this when you binaries are not accessible in the global environment.
    */
    'binary_path' => '',

    /*
    * The directory where the temporary files will be stored.
    */
    'temporary_directory' => storage_path('app/temp'),

];
