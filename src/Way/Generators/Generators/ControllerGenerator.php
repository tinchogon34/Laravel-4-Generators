<?php

namespace Way\Generators\Generators;

use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Support\Pluralizer;

class ControllerGenerator extends Generator {

    /**
     * Fetch the compiled template for a controller
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string Compiled template
     */
    protected function getTemplate($template, $className)
    {
        $this->template = $this->file->get($template);
        $resource = $this->decamelize(Pluralizer::plural(
            str_ireplace('Controller', '', $className)
        ));

        if ($this->needsScaffolding($template))
        {
            $this->template = $this->getScaffoldedController($template, $className);
        }

        $template = str_replace('{{className}}', $className, $this->template);

        return str_replace('{{collection}}', $resource, $template);
    }

    /**
     * Get template for a scaffold
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string
     */
    protected function getScaffoldedController($template, $className)
    {
        $Model = $this->cache->getModelName();  // MousePad
        $Models = Pluralizer::plural($Model);   // MousePads
        $model = $this->decamelize($Model); // mouse_pad
        $models = Pluralizer::plural($model);   // mouse_pads

        foreach(array('model', 'models', 'Models', 'Model', 'className') as $var)
        {
            $this->template = str_replace('{{'.$var.'}}', $$var, $this->template);
        }

        return $this->template;
    }

    protected static function decamelize($word)
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
