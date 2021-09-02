<?php
namespace SiteSlugAsSubdomain\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'hostname',
            'type' => Element\Text::class,
            'options' => [
                'label' => 'Hostname without subdomain *', // @translate
            ],
            'attributes' => [
                'id' => 'hostname',
            ],
        ]);
    }
}
