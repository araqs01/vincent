<?php

return [

    'catalog' => 'Каталог',

    'common' => [
        'order_index' => 'Порядок',
        'is_active' => 'Активен',
        'show_in_header' => 'Показать в Header',
    ],


    'navigation_groups' => [
        'catalog'        => 'Каталог',
        'references'     => 'Справочники',
        'site_structure' => 'Структура сайта',
        'raw_materials'  => 'Сырьё и вкусы',
    ],

    'brand' => [
        'singular' => 'Бренд',
        'plural'   => 'Бренды',
        'fields'   => [
            'name' => 'Название',
            'slug' => 'Слаг',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'manufacturer' => [
        'singular' => 'Производитель',
        'plural'   => 'Производители',
        'fields'   => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'country' => 'Страна',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'supplier' => [
        'singular' => 'Поставщик',
        'plural'   => 'Поставщики',
        'fields'   => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'email' => 'Email',
            'phone' => 'Телефон',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],




    'grape' => [
        'singular' => 'Виноград',
        'plural' => 'Винограды',
        'fields' => [
            'name' => 'Название',
            'category' => 'Категория',
            'region' => 'Регион',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'grape_variant' => [
        'singular' => 'Вкусовые хар-ки винограда ',
        'plural' => 'Вкусовые хар-ки винограда ',
        'fields' => [
            'name' => 'Название',
            'grape' => 'Виноград',
            'meta' => 'Мета данные',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'pairing' => [
        'singular' => 'Блюда',
        'plural' => 'Блюда',
        'fields' => [
            'name' => 'Название',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],


    'category_sort_group' => [

        'singular' => 'Сортировка по категориям',
        'plural' => 'Сортировки по категориям',

    ],
    'category' => [
        'singular' => 'Категория',
        'plural' => 'Категории',
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'type' => 'Тип',
            'description' => 'Описание',
            'parent' => 'Родительская категория',
        ],
        'descriptions' => [              // перенесено внутрь category
            'main' => 'Базовая информация о категории.',
            'technical' => 'Технические параметры и связи категории.',
        ],
        'hints' => [                     // перенесено внутрь category
            'parent' => 'Можно выбрать родительскую категорию.',
        ],
    ],

    'region' => [
        'singular' => 'Регион',
        'plural' => 'Регионы',
        'fields' => [
            'name' => 'Название',
            'description' => 'Описание',
            'parent' => 'Родительский регион',
            'icon_terroir' => 'Иконка терруара',
            'icon_production' => 'Иконка производства',
        ],
        'descriptions' => [
            'main' => 'Основная информация о регионе происхождения.',
            'technical' => 'Структура и иконки региона.',
            'icons' => 'Иконки, отражающие особенности региона (терруар и производство).',
        ],
        'hints' => [
            'parent' => 'Можно выбрать родительский регион (например, «Франция» для «Бордо»).',
            'icon_terroir' => 'Загрузите иконку, отражающую терруар региона.',
            'icon_production' => 'Загрузите иконку, связанную с производством (виноград, бочка и т. д.).',
        ],
    ],

    'attribute' => [
        'singular' => 'Атрибут',
        'plural' => 'Атрибуты',
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'data_type' => 'Тип данных',
            'unit' => 'Единица измерения',
            'is_filterable' => 'Использовать в фильтрах',
            'is_visible' => 'Отображать на карточке',
            'categories' => 'Категории',
        ],
        'descriptions' => [
            'main' => 'Основная информация об атрибуте.',
            'visibility' => 'Параметры отображения и тип данных.',
        ],
    ],

    'attribute_value' => [
        'singular' => 'Значение атрибута',
        'plural' => 'Значения атрибутов',
        'fields' => [
            'attribute' => 'Атрибут',
            'product' => 'Товар',
            'value' => 'Значение',
        ],
        'descriptions' => [
            'main' => 'Конкретные значения атрибутов для товаров.',
        ],
    ],

    'category_attribute' => [
        'fields' => [
            'is_required' => 'Обязательный',
            'order_index' => 'Порядок',
        ],
    ],

    'collection' => [
        'singular' => 'Подборка',
        'plural' => 'Подборки',
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'description' => 'Описание',
            'is_auto' => 'Автоматическая подборка',
            'filter_formula' => 'Формула фильтра',
        ],
        'sections' => [
            'main' => 'Основная информация',
            'settings' => 'Настройки',
        ],
    ],

    'taste_group' => [
        'singular' => 'Группа вкусов',
        'plural' => 'Группы вкусов',
        'fields' => [
            'name' => 'Название',
            'description' => 'Описание',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'taste' => [
        'singular' => 'Вкус',
        'plural' => 'Вкусы',
        'fields' => [
            'name' => 'Название',
            'group' => 'Группа вкусов',
            'weight' => 'Вес (приоритет)',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'dish_group' => [
        'singular' => 'Группа блюд',
        'plural' => 'Группы блюд',
        'fields' => [
            'name' => 'Название',
            'description' => 'Описание',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'dish' => [
        'singular' => 'Блюдо',
        'plural' => 'Блюда',
        'fields' => [
            'name' => 'Название',
            'group' => 'Группа блюд',
        ],
        'sections' => [
            'main' => 'Основная информация',
        ],
    ],

    'product' => [
        'singular' => 'Товар',
        'plural' => 'Товары',
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'description' => 'Описание',
            'category' => 'Категория',
            'region' => 'Регион',
            'supplier' => 'Поставщик',
            'base_price' => 'Базовая цена',
            'final_price' => 'Финальная цена',
            'status' => 'Статус',
            'rating' => 'Рейтинг',
            'meta' => 'Мета данные',
            'images' => 'Изображения',
        ],
        'sections' => [
            'main' => 'Основная информация',
            'classification' => 'Категории и связи',
            'pricing' => 'Цены и статус',
            'media' => 'Медиа и галерея',
            'meta' => 'Мета данные',
        ],
    ],

    'menu_block' => [
        'singular' => 'Блок меню',
        'plural' => 'Блоки меню',
        'fields' => [
            'category' => 'Категория',
            'title' => 'Заголовок',      // общий переводимый ключ
            'title_ru' => 'Заголовок (RU)',
            'title_en' => 'Заголовок (EN)',
            'type' => 'Системный тип',
            'order_index' => 'Порядок',
            'is_active' => 'Активен',
        ],
        'descriptions' => [
            'main' => 'Разделы горизонтального меню для категории.',
        ],
        'hints' => [
            'type' => 'Ключ для фронтенда, например: region, color_sugar, grape_type.',
        ],
    ],

    'menu_block_value' => [
        'singular' => 'Значение блока меню',
        'plural' => 'Значения блока меню',
        'fields' => [
            'value' => 'Значение',       // общий переводимый ключ
            'value_ru' => 'Значение (RU)',
            'value_en' => 'Значение (EN)',
            'order_index' => 'Порядок',
            'is_active' => 'Активно',
        ],
        'descriptions' => [
            'main' => 'Значения для блока меню (перечни стран, типов, сортов и т.д.).',
        ],
    ],


    'category_filter' => [
        'singular' => 'Фильтр категории',
        'plural' => 'Фильтры категорий',
        'fields' => [
            'category' => 'Категория',
            'key' => 'Ключ (системное имя)',
            'title' => 'Название',
            'mode' => 'Тип фильтра',
            'source_model' => 'Источник данных',
            'config' => 'Конфигурация',
            'is_active' => 'Активен',
            'order_index' => 'Порядок',
        ],
        'descriptions' => [
            'main' => 'Блок фильтра для категории (используется в каталоге и поиске).',
        ],
        'modes' => [
            'discrete' => 'Дискретный (варианты)',
            'reference' => 'Внешняя модель',
            'range' => 'Диапазон (min/max)',
            'boolean' => 'Булев (да/нет)',
            'attribute' => 'Атрибут товара',
        ],
        'hints' => [
            'key' => 'Уникальный ключ фильтра (например: color, sugar, grape_type).',
            'source_model' => 'Используется, если фильтр связан с внешней моделью (например, App\\Models\\Region).',
            'config' => 'Дополнительные настройки в JSON, например {"attribute_key":"color"}.',
        ],
    ],

    'category_filter_option' => [
        'singular' => 'Опция фильтра',
        'plural' => 'Опции фильтра',
        'fields' => [
            'value' => 'Значение',
            'slug' => 'Слаг',
            'meta' => 'Мета-данные',
            'is_active' => 'Активна',
            'order_index' => 'Порядок',
        ],
        'descriptions' => [
            'main' => 'Возможные значения фильтра (например: Красное, Сухое, Франция).',
        ],
    ],

];
