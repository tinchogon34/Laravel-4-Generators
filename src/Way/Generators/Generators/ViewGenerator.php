<?php

namespace Way\Generators\Generators;

use Illuminate\Support\Pluralizer;

class ViewGenerator extends Generator {

    /**
     * Fetch the compiled template for a view
     *
     * @param  string $template Path to template
     * @param  string $name
     * @return string Compiled template
     */
    protected function getTemplate($template, $name)
    {
        $this->template = $this->file->get($template);

        if ($this->needsScaffolding($template))
        {
            return $this->getScaffoldedTemplate($name);
        }

        // Otherwise, just set the file
        // contents to the file name
        return $name;
    }

    /**
     * Get the scaffolded template for a view
     *
     * @param  string $name
     * @return string Compiled template
     */
    protected function getScaffoldedTemplate($name)
    {
        $Model = $this->cache->getModelName();  // MousePad
        $Models = Pluralizer::plural($Model);   // MousePads
        $model = $this->decamelize($Model); // mouse_pad
        $models = Pluralizer::plural($model);   // mouse_pads
        

        // Create and Edit views require form elements
        if ($name === 'create.blade' or $name === 'edit.blade')
        {
            $formElements = $this->makeFormElements($model);

            $this->template = str_replace('{{formElements}}', $formElements, $this->template);
        }
        
        // Replace template vars in view
        foreach(array('model', 'models', 'Models', 'Model') as $var)
        {
            $this->template = str_replace('{{'.$var.'}}', $$var, $this->template);
        }

        // And finally create the table rows
        list($headings, $fields, $editAndDeleteLinks) = $this->makeTableRows($model);
        $this->template = str_replace('{{headings}}', implode(PHP_EOL."\t\t\t\t", $headings), $this->template);
        $this->template = str_replace('{{fields}}', implode(PHP_EOL."\t\t\t\t\t", $fields) . PHP_EOL . $editAndDeleteLinks, $this->template);

        return $this->template;
    }

    /**
     * Create the table rows
     *
     * @param  string $model
     * @return Array
     */
    protected function makeTableRows($model)
    {

        $models = Pluralizer::plural($model); // posts

        $fields = $this->cache->getFields();

        // First, we build the table headings
        $headings = array_map(function($field) use ($model) {
            return '<th>' . '{{ trans("models.attrs.'.$model.".".strtolower($field) . '") }}'.'</th>';
        }, array_keys($fields));
        $headings[] = '<th></th>';
        $headings[] = '<th></th>';

        // And then the rows, themselves
        $fields = array_map(function($field) use ($model) {
            return "<td>{{{ \$$model->$field }}}</td>";
        }, array_keys($fields));

        // Now, we'll add the edit and delete buttons.
        $editAndDelete = <<<EOT
                    <td>{{ link_to_route('{$models}.edit', trans('views.edit_button'), array(\${$model}->id), array('class' => 'btn btn-info')) }}</td>
                    <td>
                        {{ Form::open(array('method' => 'DELETE', 'route' => array('{$models}.destroy', \${$model}->id))) }}
                            {{ Form::submit(trans('views.delete_button'), array('class' => 'btn btn-danger','data-confirm'=>'¿Seguro desea eliminar?')) }}
                        {{ Form::close() }}
                    </td>
EOT;

        return array($headings, $fields, $editAndDelete);
    }

    /**
     * Add Laravel methods, as string,
     * for the fields
     *
     * @return string
     */
    public function makeFormElements($model)
    {
        $formMethods = array();

        foreach($this->cache->getFields() as $name => $type)
        {
            $formalName = ucwords($name);
            $localizedName = 'trans("models.attrs.'.$model.".".strtolower($name) . '").":"';

            // TODO: add remaining types
            switch($type)
            {
                case 'integer':

                   $element = "{{ Former::xlarge_number('$name')->label($localizedName) }}";
                    break;

                case 'text':
                    $element = "{{ Former::xlarge_text('$name')->label($localizedName) }}";
                    break;

                case 'boolean':
                    $element = "<input type='hidden' name='$name' value='0' /><br/>{{ Former::xlarge_checkbox('$name')->label($localizedName) }}";
                    break;

                default:
                    $element = "{{ Former::xlarge_text('$name')->label($localizedName) }}";
                    break;
            }

            // Now that we have the correct $element,
            // We can build up the HTML fragment

            $formMethods[] = $element;
        }

        return implode(PHP_EOL, $formMethods);
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
