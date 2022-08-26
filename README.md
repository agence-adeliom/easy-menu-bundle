
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)
[![Quality gate](https://sonarcloud.io/api/project_badges/quality_gate?project=agence-adeliom_easy-menu-bundle)](https://sonarcloud.io/dashboard?id=agence-adeliom_easy-menu-bundle)

# Easy Menu Bundle

A basic Menu system for Easyadmin.

## Installation with Symfony Flex

Add our recipes endpoint

```json
{
  "extra": {
    "symfony": {
      "endpoint": [
        "https://api.github.com/repos/agence-adeliom/symfony-recipes/contents/index.json?ref=flex/main",
        ...
        "flex://defaults"
      ],
      "allow-contrib": true
    }
  }
}
```

Install with composer

```bash
composer require agence-adeliom/easy-menu-bundle
```

### Enable tree extension

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
            type: attribute
            prefix: Gedmo\Tree\Entity
            dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Tree/Entity"
            alias: GedmoTree # (optional) it will default to the name set for the mapping
            is_bundle: false
```

### Setup database

#### Using doctrine migrations

```bash
php bin/console doctrine:migration:diff
php bin/console doctrine:migration:migrate
```

#### Without

```bash
php bin/console doctrine:schema:update --force
```

## Documentation

### Manage menu in your Easyadmin dashboard

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

### Front add a menu in a page (with twig)

```shell
# 1. Create as separate html twig file to render your template :
templates/bundles/EasyMenuBundle/front/menus/my_menu_code.html.twig

# 2. Execute this twig extension on your controller template
{{ easy_menu('my_menu_code') }}

# Optional. If you need to specify your own template file path
{{ easy_menu('my_menu_code', { 'template': 'menus/my_menu_code.html.twig' }) }}
```

### In your menu template you can list menu items

```html
<ul>
    {% for item in menu.items %}
        <li>{{ item.name }}</li>
        {# recursive loop ... #}
    {% endfor %}
</ul>
```


## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@JeromeEngelnAdeliom](https://github.com/JeromeEngelnAdeliom)

  
