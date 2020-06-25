<?php

namespace App\Console\Commands;

// use Illuminate\Console\Command;
use Illuminate\Foundation\Console\ModelMakeCommand;

class mbase extends ModelMakeCommand{
    // protected $signature = 'make:mbase';
    protected $name = 'make:mbase';

    protected $description = 'Create a new Eloquent Base model class';

    protected function resolveStubPath($stub)
    {        
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }
}
