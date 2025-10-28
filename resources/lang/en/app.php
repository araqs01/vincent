<?php

return [

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
            'main' => 'Basic information about the category.',
            'technical' => 'Technical parameters and category relations.',
        ],
        'hints' => [
            'parent' => 'You can select a parent category.',
        ],
    ],


    // en
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
            'icons' => 'Icons that reflect the specifics of the region (terroir and production).',

        ],
        'hints' => [
            'parent'          => 'You can select a parent region (e.g., "France" for "Bordeaux").',
            'icon_terroir'    => 'Upload an icon representing the region’s terroir.',
            'icon_production' => 'Upload an icon representing production (grapes, barrels, etc.).',
        ],
    ],


    // en
    'attribute' => [
        'singular' => 'Attribute',
        'plural' => 'Attributes',
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'data_type' => 'Data type',
            'unit' => 'Unit',
            'is_filterable' => 'Filterable',
            'is_visible' => 'Visible on product page',
            'categories' => 'Categories',
        ],
        'descriptions' => [
            'main' => 'Main information about the attribute.',
            'visibility' => 'Display settings and data type.',
        ],
    ],


    'attribute_value' => [
        'singular' => 'Attribute Value',
        'plural' => 'Attribute Values',
        'fields' => [
            'attribute' => 'Attribute',
            'product' => 'Product',
            'value' => 'Value',
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
        'plural' => 'Collections',
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'is_auto' => 'Automatic',
            'filter_formula' => 'Filter formula',
        ],
        'sections' => [
            'main' => 'Main information',
            'settings' => 'Settings',
        ],
    ],

    'taste_group' => [
        'singular' => 'Taste Group',
        'plural' => 'Taste Groups',
        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'taste' => [
        'singular' => 'Taste',
        'plural' => 'Tastes',
        'fields' => [
            'name' => 'Name',
            'group' => 'Taste group',
            'weight' => 'Weight (priority)',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'dish_group' => [
        'singular' => 'Dish Group',
        'plural' => 'Dish Groups',
        'fields' => [
            'name' => 'Name',
            'description' => 'Description',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'dish' => [
        'singular' => 'Dish',
        'plural' => 'Dishes',
        'fields' => [
            'name' => 'Name',
            'group' => 'Dish group',
        ],
        'sections' => [
            'main' => 'Main information',
        ],
    ],

    'product' => [
        'singular' => 'Product',
        'plural' => 'Products',
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
            'category' => 'Category',
            'region' => 'Region',
            'supplier' => 'Supplier',
            'base_price' => 'Base price',
            'final_price' => 'Final price',
            'status' => 'Status',
            'rating' => 'Rating',
            'meta' => 'Meta data',
            'images' => 'Images',
        ],
        'sections' => [
            'main' => 'Main information',
            'classification' => 'Classification',
            'pricing' => 'Pricing and status',
            'media' => 'Media and gallery',
            'meta' => 'Meta data',
        ],
    ],

];
