
![Adeliom](https://adeliom.com/public/uploads/2017/09/Adeliom_logo.png)

# Easy Menu Bundle

A basic Menu system for Easyadmin.

## Installation

Install with composer

```bash
composer require agence-adeliom/easy-menu-bundle
```

Update `config/packages/stof_doctrine_extensions.yaml to add gedmo tree mapping configuration`:

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

## Documentation

## License

[MIT](https://choosealicense.com/licenses/mit/)


## Authors

- [@JeromeEngelnAdeliom](https://github.com/JeromeEngelnAdeliom)

  
