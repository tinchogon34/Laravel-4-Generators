<?php

namespace Way\Generators\Generators;

use Illuminate\Support\Pluralizer;

class TestGenerator extends Generator {

    /**
     * Fetch the compiled template for a test
     *
     * @param  string $template Path to template
     * @param  string $className
     * @return string Compiled template
     */
    protected function getTemplate($template, $className)
    {
        $Model = $this->cache->getModelName();  // MousePad
        $Models = Pluralizer::plural($Model);   // MousePads
        $model = $this->decamelize($Model); // mouse_pad
        $models = Pluralizer::plural($model);   // mouse_pads


        $template = $this->file->get($template);

        foreach(array('model', 'models', 'Models', 'Model', 'className') as $var)
        {
            $template = str_replace('{{'.$var.'}}', $$var, $template);
        }

        return $template;
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
