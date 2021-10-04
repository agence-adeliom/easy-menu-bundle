# Enable tree extension

Update `config/packages/stof_doctrine_extensions.yaml` to add gedmo tree mapping configuration:

``` yaml
stof_doctrine_extensions:
  orm:
    default:
      tree: true
      
doctrine:
  orm:
    entity_managers:
      default:
        mappings:
          gedmo_tree:
            type: annotation
            prefix: Gedmo\Tree\Entity
            dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Tree/Entity"
            alias: GedmoTree # (optional) it will default to the name set for the mapping
            is_bundle: false
```

# Setup database

## Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

## Without

```bash
php bin/console doctrine:schema:update --force
```

# Manage menu in your Easyadmin dashboard

Go to your dashboard controller, example : `src/Controller/Admin/DashboardController.php`

```php
<?php

namespace App\Controller\Admin;

...
use App\Entity\EasyMenu\Menu;

class DashboardController extends AbstractDashboardController
{
    ...
    public function configureMenuItems(): iterable
    {
        ...
        yield MenuItem::linkToCrud('easy.menu.admin.menus', 'fa fa-file-alt', Menu::class);

        ...
```
