<?php

namespace SiteSlugAsSubdomain;

use Omeka\Module\AbstractModule;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Renderer\PhpRenderer;
use Omeka\Api\Exception\NotFoundException;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

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
     * @return ViewModel The view
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $view = new ViewModel();
        $view->setTemplate("config_form");

        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $hostname = $settings->get('hostname');

        $view->setVariable('hostname', $hostname);
        return $renderer->render($view);
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

        $params = $controller->getRequest()->getPost();
        $settings->set('hostname', $params['hostname']);
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

        // Replace the "/s/site-slug" type (\Zend\Router\Http\Segment) by a dynamic subdomain type (\Zend\Router\Http\Hostname)
        $route['type'] = \Zend\Router\Http\Hostname::class;

        // Define the route
        $hostname = urlencode($hostname);
        $route['options']['route'] = ':site-slug.' . $hostname;

        // Override site slug constraints for safety reasons and to avoid issues with subdomains which are not Omeka sites (e.g. "www.")
        $route['options']['constraints']['site-slug'] = $authorizedSlugsRegExp;

        // Handle the "/" route
        $route['child_routes']['home'] = [
                                            'type' => \Zend\Router\Http\Segment::class,
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
