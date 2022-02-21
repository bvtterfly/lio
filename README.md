# Easily optimize images using Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bvtterfly/lio.svg?style=flat-square)](https://packagist.org/packages/bvtterfly/lio)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/bvtterfly/lio/run-tests?label=tests)](https://github.com/bvtterfly/lio/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/bvtterfly/lio/Check%20&%20fix%20styling?label=code%20style)](https://github.com/bvtterfly/lio/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bvtterfly/lio?style=flat-square)](https://packagist.org/packages/bvtterfly/lio)

This package can optimize PNGs, JPGs, SVGs, and GIFs by running them through a chain of various [image optimization tools](https://github.com/bvtterfly/lio#optimization-tools).
It is heavily based on `Spatie`'s `spatie/image-optimizer` and `spatie/laravel-image-optimizer` packages.
Unlike them, It works with Laravel's filesystem disks, and it'll automatically take care of the download & upload of your images.

Here's how you can use it:

```php
use Bvtterfly\Lio\Facades\ImageOptimizer;
// the image will be optimized and stored in the output path  
ImageOptimizer::optimize($pathToImage, $pathToOptimizedImage);
```
If you don't like facades, just resolve a configured instance of `Bvtterfly\Lio\OptimizerChain` out of the container:

```php
use Bvtterfly\Lio\OptimizerChain;
app(OptimizerChain::class)->optimize($pathToImage, $pathToOptimizedImage);
```

## Installation

You can install the package via composer:

```bash
composer require bvtterfly/lio
```

The package will automatically register itself.

The package uses a bunch of binaries to optimize images. To learn which ones on how to install them, head over to the optimization tools section in the readme of the underlying image-optimizer package. That readme also contains info on what these tools will do to your images.

The package comes with some sane defaults to optimize images. You can modify that configuration by publishing the config file.

```bash
php artisan vendor:publish --tag="laravel-image-optimizer-config"
```

This is the contents of the published config file:

```php
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
```
### Optimization tools

The package will use these optimizers if they are present on your system:

- [JpegOptim](http://freecode.com/projects/jpegoptim)
- [Optipng](http://optipng.sourceforge.net/)
- [Pngquant 2](https://pngquant.org/)
- [SVGO 1](https://github.com/svg/svgo)
- [Gifsicle](http://www.lcdf.org/gifsicle/)
- [cwebp](https://developers.google.com/speed/webp/docs/precompiled)

Here's how to install all the optimizers on Ubuntu:

```bash
sudo apt-get install jpegoptim
sudo apt-get install optipng
sudo apt-get install pngquant
sudo npm install -g svgo@1.3.2
sudo apt-get install gifsicle
sudo apt-get install webp
```

And here's how to install the binaries on MacOS (using [Homebrew](https://brew.sh/)):

```bash
brew install jpegoptim
brew install optipng
brew install pngquant
npm install -g svgo@1.3.2
brew install gifsicle
brew install webp
```
And here's how to install the binaries on Fedora/RHEL/CentOS:

```bash
sudo dnf install epel-release
sudo dnf install jpegoptim
sudo dnf install optipng
sudo dnf install pngquant
sudo npm install -g svgo@1.3.2
sudo dnf install gifsicle
sudo dnf install libwebp-tools
```

## Which tools will do what?

The package will automatically decide which tools to use on a particular image.

### JPGs

JPGs will be made smaller by running them through [JpegOptim](http://freecode.com/projects/jpegoptim). These options are used:
- `-m85`: this will store the image with 85% quality. This setting [seems to satisfy Google's Pagespeed compression rules](https://webmasters.stackexchange.com/questions/102094/google-pagespeed-how-to-satisfy-the-new-image-compression-rules)
- `--strip-all`: this strips out all text information such as comments and EXIF data
- `--all-progressive`: this will make sure the resulting image is a progressive one, meaning it can be downloaded using multiple passes of progressively higher details.

### PNGs

PNGs will be made smaller by running them through two tools. The first one is [Pngquant 2](https://pngquant.org/), a lossy PNG compressor. We set no extra options, their defaults are used. After that we run the image through a second one: [Optipng](http://optipng.sourceforge.net/). These options are used:
- `-i0`: this will result in a non-interlaced, progressive scanned image
- `-o2`: this set the optimization level to two (multiple IDAT compression trials)

### SVGs

SVGs will be minified by [SVGO 1](https://github.com/svg/svgo). SVGO's default configuration will be used, with the omission of the `cleanupIDs` plugin because that one is known to cause troubles when displaying multiple optimized SVGs on one page.

Please be aware that SVGO can break your svg. You'll find more info on that in this [excellent blogpost](https://www.sarasoueidan.com/blog/svgo-tools/) by [Sara Soueidan](https://twitter.com/SaraSoueidan).

For now, the default configuration used for SVGO is only compatible with SVGO 1.x. To use options compatible with SVGO 2.x, you need to [create your own optimization chain](#creating-your-own-optimization-chains).

### GIFs

GIFs will be optimized by [Gifsicle](http://www.lcdf.org/gifsicle/). These options will be used:
- `-O3`: this sets the optimization level to Gifsicle's maximum, which produces the slowest but best results

### WEBPs

WEBPs will be optimized by [Cwebp](https://developers.google.com/speed/webp/docs/cwebp). These options will be used:
- `-m 6` for the slowest compression method in order to get the best compression.
- `-pass 10` for maximizing the amount of analysis pass.
- `-mt` multithreading for some speed improvements.
- `-q 90` Quality factor that brings the least noticeable changes.

(Settings are original taken from [here](https://medium.com/@vinhlh/how-i-apply-webp-for-optimizing-images-9b11068db349))


## Usage
You can resolve a configured instance of configured instance of `Bvtterfly\Lio\OptimizerChain` out of the container:
```php
use Bvtterfly\Lio\OptimizerChain;
app(OptimizerChain::class)->optimize($pathToImage, $pathToOptimizedImage);
```
or using facade:

```php
use Bvtterfly\Lio\Facades\ImageOptimizer;
// the image will be optimized and stored in the output path  
ImageOptimizer::optimize($pathToImage, $pathToOptimizedImage);
```

### Writing a custom optimizers

If you want to write your optimizer and optimize your images using another command-line utility, write your optimizer. An optimizer is any class that implements the `Bvtterfly\Lio\Optimizer` interface:

```php
namespace Bvtterfly\Lio;

use Bvtterfly\Lio\TempImage;

interface Optimizer
{
    /**
     * Returns the name of the binary to be executed.
     *
     * @return string
     */
    public function binaryName(): string;

    /**
     * Determines if the given image can be handled by the optimizer.
     *
     * @param TempImage $image
     *
     * @return bool
     */
    public function canHandle(TempImage $image): bool;

    /**
     * Set the path to the image that should be optimized.
     *
     * @param string $imagePath
     *
     * @return $this
     */
    public function setImagePath(string $imagePath);

    /**
     * Set the options the optimizer should use.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = []);

    /**
     * Get the command that should be executed.
     *
     * @return string
     */
    public function getCommand(): string;
}
```

If you want to view an example implementation take a look at [the existing optimizers](https://github.com/bvtterfly/lio/tree/main/src/Optimizers) shipped with this package.
You can add the fully qualified classname of your optimizer as a key in the `optimizers` array in the config file.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [ARI](https://github.com/bvtterfly)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
