<?php

use Bvtterfly\Lio\Optimizers\Jpegoptim;
use Illuminate\Support\Facades\Config;

it('can accept options via the constructor', function () {
    $optimizer = (new Jpegoptim(['option1', 'option2']))->setImagePath('my-image.jpg');
    expect($optimizer)
        ->getCommand()
        ->toBe("\"jpegoptim\" option1 option2 'my-image.jpg'");
});

it('can set a binary path', function () {
    Config::set('lio.binaries_path.jpegoptim', 'testPath');
    $optimizer = (new Jpegoptim([]))
        ->setImagePath('my-image.jpg');

    expect($optimizer)
        ->getCommand()
        ->toBe("\"testPath/jpegoptim\"  'my-image.jpg'");

    Config::set('lio.binaries_path.jpegoptim', 'testPath/');

    $optimizer = (new Jpegoptim([]))
        ->setImagePath('my-image.jpg');

    expect($optimizer)
        ->getCommand()
        ->toBe("\"testPath/jpegoptim\"  'my-image.jpg'");

    Config::set('lio.binaries_path.jpegoptim', '');
    $optimizer = (new Jpegoptim([]))
        ->setImagePath('my-image.jpg');

    expect($optimizer)
        ->getCommand()
        ->toBe("\"jpegoptim\"  'my-image.jpg'");
});

it('can override arguments', function () {
    $optimizer = (new Jpegoptim(['argument1', 'argument2']))->setImagePath('my-image.jpg');
    $optimizer->setArguments(['argument3', '--argument4']);
    expect($optimizer)
        ->getCommand()
        ->toBe("\"jpegoptim\" argument3 --argument4 'my-image.jpg'");
});


it('can get jpeg binary name', function () {
    $optimizer = (new Jpegoptim())->setImagePath('my-image.jpg');

    $optimizer->setArguments(['argument3', '--argument4']);
    expect($optimizer)
        ->binaryName()
        ->toBe('jpegoptim');
});
