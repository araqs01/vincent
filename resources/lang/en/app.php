<?php

return [

    'catalog' => 'Catalog',

    'common' => [
        'order_index' => 'Order',
        'is_active'   => 'Active',
        'show_in_header' => 'Show in Header Header',
    ],

    'navigation_groups' => [
        'catalog'        => 'Catalog',
        'references'     => 'References',
        'site_structure' => 'Site Structure',
        'raw_materials'  => 'Raw Materials & Tastes',
    ],

    'brand' => [
        'singular' => 'Brand',
        'plural'   => 'Brands',
        'fields'   => [
            'name' => 'Name',
            'slug' => 'Slug',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'manufacturer' => [
        'singular' => 'Manufacturer',
        'plural'   => 'Manufacturers',
        'fields'   => [
            'name' => 'Name',
            'slug' => 'Slug',
            'country' => 'Country',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'supplier' => [
        'singular' => 'Supplier',
        'plural'   => 'Suppliers',
        'fields'   => [
            'name' => 'Name',
            'slug' => 'Slug',
            'email' => 'Email',
            'phone' => 'Phone',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],


    'grape' => [
        'singular' => 'Grape',
        'plural' => 'Grapes',
        'fields' => [
            'name' => 'Name',
            'category' => 'Category',
            'region' => 'Region',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'grape_variant' => [
        'singular' => 'Grape Variety',
        'plural' => 'Grape Varieties',
        'fields' => [
            'name' => 'Name',
            'grape' => 'Grape',
            'meta' => 'Metadata',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'pairing' => [
        'singular' => 'Pairing',
        'plural' => 'Pairings',
        'fields' => [
            'name' => 'Name',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],


    'category' => [
        'singular' => 'Category',
        'plural'   => 'Categories',
        'fields'   => [
            'name'        => 'Name',
            'slug'        => 'Slug',
            'type'        => 'Type',
            'description' => 'Description',
            'parent'      => 'Parent Category',
        ],
        'descriptions' => [
            'main'      => 'Basic information about the category.',
            'technical' => 'Technical parameters and category relations.',
        ],
        'hints' => [
            'parent' => 'You can select a parent category.',
        ],
    ],

    'region' => [
        'singular' => 'Region',
        'plural'   => 'Regions',
        'fields'   => [
            'name'            => 'Name',
            'description'     => 'Description',
            'parent'          => 'Parent region',
            'icon_terroir'    => 'Terroir icon',
            'icon_production' => 'Production icon',
        ],
        'descriptions' => [
            'main'      => 'Main information about the region of origin.',
            'technical' => 'Region structure and icons.',
            'icons'     => 'Icons that reflect the specifics of the region (terroir and production).',
        ],
        'hints' => [
            'parent'          => 'You can select a parent region (e.g., "France" for "Bordeaux").',
            'icon_terroir'    => 'Upload an icon representing the region’s terroir.',
            'icon_production' => 'Upload an icon representing production (grapes, barrels, etc.).',
        ],
    ],

    'attribute' => [
        'singular' => 'Attribute',
        'plural'   => 'Attributes',
        'fields'   => [
            'name'         => 'Name',
            'slug'         => 'Slug',
            'data_type'    => 'Data type',
            'unit'         => 'Unit',
            'is_filterable'=> 'Filterable',
            'is_visible'   => 'Visible on product page',
            'categories'   => 'Categories',
        ],
        'descriptions' => [
            'main'       => 'Main information about the attribute.',
            'visibility' => 'Display settings and data type.',
        ],
    ],

    'attribute_value' => [
        'singular' => 'Attribute Value',
        'plural'   => 'Attribute Values',
        'fields'   => [
            'attribute' => 'Attribute',
            'product'   => 'Product',
            'value'     => 'Value',
        ],
        'descriptions' => [
            'main' => 'Specific attribute values for products.',
        ],
    ],

    'category_attribute' => [
        'fields' => [
            'is_required' => 'Required',
            'order_index' => 'Order',
        ],
    ],

    'collection' => [
        'singular' => 'Collection',
        'plural'   => 'Collections',
        'fields'   => [
            'name'           => 'Name',
            'slug'           => 'Slug',
            'description'    => 'Description',
            'is_auto'        => 'Automatic',
            'filter_formula' => 'Filter formula',
        ],
        'sections' => [
            'main'     => 'Main information',
            'settings' => 'Settings',
        ],
    ],

    'taste_group' => [
        'singular' => 'Taste Group',
        'plural'   => 'Taste Groups',
        'fields'   => [
            'name'        => 'Name',
            'description' => 'Description',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'taste' => [
        'singular' => 'Taste',
        'plural'   => 'Tastes',
        'fields'   => [
            'name'   => 'Name',
            'group'  => 'Taste group',
            'weight' => 'Weight (priority)',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'dish_group' => [
        'singular' => 'Dish Group',
        'plural'   => 'Dish Groups',
        'fields'   => [
            'name'        => 'Name',
            'description' => 'Description',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'dish' => [
        'singular' => 'Dish',
        'plural'   => 'Dishes',
        'fields'   => [
            'name'  => 'Name',
            'group' => 'Dish group',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'product' => [
        'singular' => 'Product',
        'plural'   => 'Products',
        'fields'   => [
            'name'        => 'Name',
            'slug'        => 'Slug',
            'description' => 'Description',
            'category'    => 'Category',
            'region'      => 'Region',
            'supplier'    => 'Supplier',
            'base_price'  => 'Base price',
            'final_price' => 'Final price',
            'status'      => 'Status',
            'rating'      => 'Rating',
            'meta'        => 'Meta data',
            'images'      => 'Images',
        ],
        'sections' => [
            'main'           => 'Main information',
            'classification' => 'Classification',
            'pricing'        => 'Pricing and status',
            'media'          => 'Media and gallery',
            'meta'           => 'Meta data',
        ],
    ],

    'menu_block' => [
        'singular' => 'Menu Block',
        'plural'   => 'Menu Blocks',
        'fields'   => [
            'category'    => 'Category',
            'title'       => 'Title',          // generic translatable field
            'title_ru'    => 'Title (RU)',
            'title_en'    => 'Title (EN)',
            'type'        => 'System type',
            'order_index' => 'Order',
            'is_active'   => 'Active',
        ],
        'descriptions' => [
            'main' => 'Horizontal menu sections for a category.',
        ],
        'hints' => [
            'type' => 'Frontend key, e.g. region, color_sugar, grape_type.',
        ],
    ],

    'menu_block_value' => [
        'singular' => 'Menu Block Value',
        'plural'   => 'Menu Block Values',
        'fields'   => [
            'value'       => 'Value',          // generic translatable field
            'value_ru'    => 'Value (RU)',
            'value_en'    => 'Value (EN)',
            'order_index' => 'Order',
            'is_active'   => 'Active',
        ],
        'descriptions' => [
            'main' => 'Values for a menu block (countries, types, grapes, etc.).',
        ],
    ],

    'category_filter' => [
        'singular' => 'Category Filter',
        'plural'   => 'Category Filters',
        'fields'   => [
            'category'     => 'Category',
            'key'          => 'Key (system name)',
            'title'        => 'Title',
            'mode'         => 'Filter Mode',
            'source_model' => 'Source Model',
            'config'       => 'Configuration',
            'is_active'    => 'Active',
            'order_index'  => 'Order',
        ],
        'descriptions' => [
            'main' => 'Filter block for a category (used for search & catalog filtering).',
        ],
        'modes' => [
            'discrete'   => 'Discrete (options)',
            'reference'  => 'Reference (external model)',
            'range'      => 'Range (min/max)',
            'boolean'    => 'Boolean (yes/no)',
            'attribute'  => 'Attribute (product field)',
        ],
        'hints' => [
            'key'          => 'Unique identifier for this filter (e.g. color, sugar, grape_type).',
            'source_model' => 'Used only if the filter type is Reference (e.g. App\\Models\\Region).',
            'config'       => 'Optional JSON settings, such as { "attribute_key": "color" }.',
        ],
    ],

    'category_filter_option' => [
        'singular' => 'Filter Option',
        'plural'   => 'Filter Options',
        'fields'   => [
            'value'       => 'Value',
            'slug'        => 'Slug',
            'meta'        => 'Metadata',
            'is_active'   => 'Active',
            'order_index' => 'Order',
        ],
        'descriptions' => [
            'main' => 'Available selectable values for a filter (e.g. Red, Dry, France).',
        ],
    ],

];
