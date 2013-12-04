<?php

namespace Way\Generators\Generators;

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Pluralizer;

class ResourceGenerator {

    /**
     * File system instance
     *
     * @var File
     */
    protected $file;

    /**
     * Constructor
     *
     * @param $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Update app/routes.php
     *
     * @param  string $name
     * @return void
     */
    public function updateRoutesFile($name)
    {
        $name = Pluralizer::plural($name);

        $this->file->append(
            app_path() . '/routes.php',
            "\n\nRoute::resource('" . $this->decamelize($name) . "', '" . $name . "Controller');"
        );
    }

    /**
     * Create any number of folders
     *
     * @param  string|array $folders
     * @return void
     */
    public function folders($folders)
    {
        foreach((array) $folders as $folderPath)
        {
            if (! $this->file->exists($folderPath))
            {
                $this->file->makeDirectory($folderPath);
            }
        }
    }

    public static function decamelize($word)
    {
        $callback = create_function('$matches',
            'return strtolower(strlen("$matches[1]") ? "$matches[1]_$matches[2]" : "$matches[2]");');

        return preg_replace_callback(
            '/(^|[a-z])([A-Z])/',
            $callback,
            $word
        );
    }
}
