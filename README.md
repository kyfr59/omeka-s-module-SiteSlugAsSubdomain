SiteSlugAsSubdomain (module for Omeka S)
=======================================

[SiteSlugAsSubdomain] is a module for [Omeka S] that allows users to map the Omeka S
sites slugs as subdomains.

For example, if your Omeka S domain is [www.myapp.com](http://www.myapp.com "www.myapp.com") and your sites URLs are :
[www.myapp.com/s/my-first-site](www.myapp.com/s/my-first-site "www.myapp.com/s/my-first-site") and [www.myapp.com/s/my-second-site](www.myapp.com/s/my-second-site "www.myapp.com/s/my-second-site"),
they will become [my-first-site.myapp.com](my-first-site.myapp.com "my-first-site.myapp.com") and [my-second-site.myapp.com](my-second-site.myapp.com "my-second-site.myapp.com").


Installation
------------

Uncompress the zip inside the folder `modules` and rename it `SiteSlugAsSubdomain`.

See general end user documentation for [Installing a module].

Requirements
------------

- The hosts must be configured on your webserver ([my-first-site.myapp.com](my-first-site.myapp.com "my-first-site.myapp.com") and ([my-second-site.myapp.com](my-second-site.myapp.com "my-first-site.myapp.com")  must answer the ping).
- Your sites must be defined as public.
- Your must set your hostname in the module configuration (see below).

Config
------

You must provide your hostname in the module configuration, otherwise the subdomain mapping will not run.

You must provide your hostname **without subdomain**, here are some examples :

http://www.example.com => example.com
http://www.example.com/ => example.com
http://www.test.example.com => test.example.com
http://www.example.co.uk => example.co.uk
http://localhost => locahost

Once installed, the Omeka S admin dashboard remains accessible at its original address.

The changes are visible in the "Sites" section of the Omeka S dashboard.

##### Notes

- Your hostname can't be an IP address (you can install Omeka S on http://192.168.1.12 but you can't have http://my-first-site.192.168.1.12).
- The module isn't compatible with the subdirectories installations (webserver aliases) at this time, for example http://www.yourapp.com/omeka-s/
- Ensure to enter your hostname in the module config withtout the potential subdomain (i.e. **www.**)


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


License
-------

This plugin is published under [GNU/GPL].

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

**Human interface** (xslt stylesheet)

The human interface is published under the [CeCILL-B] BSD-like licence. See its
header for other licenses notes.

Acknowledgments
---------

This plugin was built by exploring various Daniel Berthereau's plugins, (see [Daniel-KM] on GitHub).

Copyright
---------

* Copyright **Franck Dupont**, 2019

[SiteSlugAsSubdomain]: https://github.com/kyfr59/omeka-s-module-SiteSlugAsSubdomain
[Omeka S]: https://omeka.org/s
[Omeka]: https://omeka.org/classic
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[module issues]: https://github.com/kyfr59/omeka-s-module-SiteSlugAsSubdomain/issues
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[CeCILL-B]: https://www.cecill.info/licences/Licence_CeCILL-B_V1-en.html
[Franck Dupont]: https://github.com/kyfr59/ "Franck Dupont"
[Daniel-KM]: https://github.com/Daniel-KM/
