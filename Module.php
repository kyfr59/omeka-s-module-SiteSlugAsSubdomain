<?php

namespace SiteSlugAsSubdomain;

use Omeka\Module\AbstractModule;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Exception\NotFoundException;
use SiteSlugAsSubdomain\Form\ConfigForm;

class Module extends AbstractModule
{

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        // Create the route only if the hostname is filled in the module configuration
        if ($this->getHostnameFromSettings()) {
            $this->addSubdomainRoute($this->getHostnameFromSettings());
        }
    }

    /**
     * Load the config form
     *
     * @param PhpRenderer $renderer
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $translator = $this->getServiceLocator()->get('MvcTranslator');
        $form = new ConfigForm;
        $form->init();
        $form->setData([
            'hostname' => $settings->get('hostname'),
        ]);
        $html = <<<EOD
        <h1>SiteSlugAsSubdomain module</h1>

        <p>
          {$translator->translate('This module allows to considerer sites slugs as subdomains rather than URL params')}.<br />
          {$translator->translate('So, the original URL <strong>www.myapp.com/s/mysite</strong> will become <strong>mysite.myapp.com</strong>')}.<br />
          {$translator->translate('The Omeka admin dashboard will always be accessible at its original address (e.g. <strong>www.myapp.com/admin</strong>)')}.
        </p>

        <h3>{$translator->translate('Requirements')}</h3>
        <ul>
          <li>{$translator->translate('The hosts must be configured on your webserver (<strong>mysite.myapp.com</strong> must anwser the ping).')}</li>
          <li>{$translator->translate('Your sites must be <a target="_blank" href="https://omeka.org/s/docs/user-manual/sites/#publication-settings">defined as public</a>.')}</li>
          <li>{$translator->translate('Your must set your hostname below')}.</li>
        </ul>
        <h3>{$translator->translate('Hostname')}</h3>
        <p>
          {$translator->translate('Your hostname <b>without subdomain</b> is needed to enable subdomains')}.<br /><br />
          {$translator->translate('Here are some examples')}:
          <ul>
            <li>http://www.example.com => <strong>example.com</strong></li>
            <li>http://www.example.com/ => <strong>example.com</strong></li>
            <li>http://www.test.example.com => <strong>test.example.com</strong></li>
            <li>http://www.example.co.uk => <strong>example.co.uk</strong></li>
            <li>http://localhost => <strong>locahost</strong></li>
          </ul>
        </p>
EOD;
        $html .= $renderer->formCollection($form, false);
        return $html;
    }

    /**
     * Retrieve the hostname filled in the module configuration (and stored in the Omeka's settings)
     *
     * @return String The hostname
     */
     public function getHostnameFromSettings()
     {
         $settings = $this->getServiceLocator()->get('Omeka\Settings');
         return $settings->get('hostname');
     }

    /**
     * Store the hostname in the Omeka settings
     *
     * @return String The hostname
     */
     public function handleConfigForm(AbstractController $controller)
     {
         $settings = $this->getServiceLocator()->get('Omeka\Settings');
         $form = new ConfigForm;
         $form->init();
         $form->setData($controller->params()->fromPost());
         if (!$form->isValid()) {
             $controller->messenger()->addErrors($form->getMessages());
             return false;
         }
         $formData = $form->getData();
         $settings->set('hostname', $formData['hostname']);
         return true;
     }

    /**
     * Handle the subdomains routing by updating the original Omeka's route
     *
     * @param String $hostname The hostname
     */
    public function addSubdomainRoute(String $hostname)
    {
        $config = $this->getServiceLocator()->get('Config');
        $router = $this->getServiceLocator()->get('Router');

        // Retrive the original 'site' routes array
        $route = $config['router']['routes']['site'];

        $authorizedSlugsRegExp = $this->getSitesSlugsRegExp();

        // Replace the "/s/site-slug" type (\Laminas\Router\Http\Segment) by a dynamic subdomain type (\Laminas\Router\Http\Hostname)
        $route['type'] = \Laminas\Router\Http\Hostname::class;

        // Define the route
        $hostname = urlencode($hostname);
        $route['options']['route'] = ':site-slug.' . $hostname;

        // Override site slug constraints for safety reasons and to avoid issues with subdomains which are not Omeka sites (e.g. "www.")
        $route['options']['constraints']['site-slug'] = $authorizedSlugsRegExp;

        // Handle the "/" route
        $route['child_routes']['home'] = [
                                            'type' => \Laminas\Router\Http\Segment::class,
                                            'options' => [
                                                'route' => '/',
                                                'defaults' => [
                                                    'action' => 'index',
                                                ],
                                            ],
                                        ];

        // Update the route
        $router->addRoute('site', $route);
    }

    /**
     * Generate a regular expression which represents the allowed subdomains,
     * for example : ($first-slug)|($second-slug)
     *
     * @return String a regular expression
     */
    public function getSitesSlugsRegExp()
    {
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');

        $sites = $api->search('sites')->getContent();

        if (!$sites) {
            throw new NotFoundException("Page not found, are you sure your site is public ?", 2);
        }

        $regexp = '';
        foreach ($sites as $site) {
            $regexp .= '(^'.$site->slug().')|';
        }

        return rtrim($regexp, '|');
    }
}
