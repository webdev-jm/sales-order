<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Sales Management System',
    'title_prefix' => 'SMS | ',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'SMS',
    'logo_img' => 'images\sales-order-logo2.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'SMS',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'images\sales-order-logo2.png',
            'alt' => 'Sales Management System',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'img' => [
            'path' => 'images\sales-order-logo2.png',
            'alt' => 'SMS',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-success',
    'usermenu_image' => false,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => '',
    'classes_auth_header' => 'bg-gradient-success',
    'classes_auth_body' => '',
    'classes_auth_footer' => 'text-center',
    'classes_auth_icon' => 'fa-lg text-success',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => 'bg-success',
    'classes_brand_text' => 'text-white font-weight-bold',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-light-olive elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 's',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        // [
        //     'type'         => 'navbar-search',
        //     'text'         => 'search',
        //     'topnav_right' => true,
        // ],
        // [
        //     'type'         => 'fullscreen-widget',
        //     'topnav_right' => true,
        // ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text'        => 'Home',
            'url'         => 'home',
            'icon'        => 'fas fa-fw fa-home',
        ],
        [
            'text'      => 'Dashboard',
            'url'       => 'dashboard',
            'icon'      => 'fas fa-fw fa-chart-pie',
            'active'    => ['dashboard*']
        ],
        [
            'text'      => 'remittances',
            'url'       => 'remittance',
            'icon'      => 'fas fa-fw fa-chart-pie',
            'active'    => ['remittance*']
        ],
        [
            'text'      => 'Purchase Orders',
            'url'       => '/purchase-order',
            'icon'      => 'fas fa-fw fa-shopping-cart',
            'can'       => 'purchase order access',
            'active'    => ['purchase-order*']
        ],
        [
            'text'      => 'Sales Orders',
            'url'       => '/sales-order',
            'icon'      => 'fas fa-fw fa-cart-plus',
            'can'       => 'sales order access',
            'active'    => ['sales-order*']
        ],
        [
            'text'      => 'Sales Order List',
            'url'       => '/list-sales-order/list',
            'icon'      => 'fas fa-fw fa-list',
            'can'       => 'sales order list',
            'active'    => ['list-sales-order*']
        ],
        [
            'text'      => 'Invoice',
            'url'       => '/invoice',
            'icon'      => 'fas fa-fw fa-list',
            'can'       => 'invoice access',
            'active'    => ['invoice']
        ],
        [
            'text'      => 'Schedules',
            'url'       => '/schedule',
            'icon'      => 'fas fa-fw fa-calendar-alt',
            'can'       => 'schedule access',
            'active'    => ['schedule*']
        ],
        [
            'text'      => 'Activity Plan',
            'url'       => '/mcp',
            'icon'      => 'fas fa-fw fa-clock',
            'can'       => 'mcp access',
            'active'    => ['mcp*']
        ],
        [
            'text'      => 'Trips',
            'url'       => '/trip',
            'icon'      => 'fas fa-fw fa-plane',
            'can'       => 'trip access',
            'active'    => ['trip*']
        ],
        [
            'text'      => 'PAF',
            'url'       => '/paf',
            'icon'      => 'fas fa-fw fa-list',
            'can'       => 'paf access',
            'active'    => ['paf*']
        ],
        [
            'text'      => 'Pre Plan',
            'url'       => '/pre-plan',
            'icon'      => 'fas fa-fw fa-balance-scale-right',
            'can'       => 'pre plan access',
            'active'    => ['pre-plan*']
        ],
        [
            'text'      => 'PAF Activities',
            'url'       => '/paf-activity',
            'icon'      => 'fas fa-fw fa-balance-scale-right',
            'can'       => 'paf activity access',
            'active'    => ['paf-activity*']
        ],
        [
            'text'      => 'Weekly Productivity Report',
            'url'       => '/war',
            'icon'      => 'fas fa-fw fa-calendar-week',
            'can'       => 'war access',
            'active'    => ['war*']
        ],
        [
            'text'      => 'Reports',
            'url'       => '/report',
            'icon'      => 'fas fa-fw fa-chart-bar',
            'can'       => 'report access',
            'active'    => ['report*']
        ],
        [
            'text'      => 'MCP Reports',
            'url'       => '/combined/report',
            'icon'      => 'fas fa-fw fa-chart-pie',
            'can'       => 'report access',
            'active'    => ['combined*']
        ],
        [
            'text'      => 'Productivity Reports',
            'url'       => '/productivity-report',
            'icon'      => 'fas fa-fw fa-chart-line',
            'can'       => 'productivity report access',
            'active'    => ['productivity-report*']
        ],
        [
            'text'      => 'Salesmen',
            'url'       => '/salesman',
            'icon'      => 'fas fa-fw fa-users',
            'can'       => 'salesman access',
            'active'    => ['salesman', 'salesman/*']
        ],
        [
            'text'      => 'Salesman Locations',
            'url'       => '/salesman-location',
            'icon'      => 'fas fa-fw fa-map',
            'can'       => 'salesman location access',
            'active'    => ['salesman-location', 'salesman-location/*']
        ],
        [
            'text'      => 'COE Reports',
            'url'       => '/channel-operation',
            'icon'      => 'fas fa-fw fa-window-restore',
            'can'       => 'channel operation report',
            'active'    => ['channel-operation*']
        ],
        // DMS
        [
            'header'    => 'DMS',
            'can'       => [
                'district access',
                'territory access',
            ],
        ],
        [
            'text'      => 'Districts',
            'url'       => '/district',
            'icon'      => 'fas fa-fw fa-network-wired',
            'can'       => 'district access',
            'active'    => ['district*']
        ],
        [
            'text'      => 'Territories',
            'url'       => '/territory',
            'icon'      => 'fas fa-fw fa-map-pin',
            'can'       => 'territory access',
            'active'    => ['territory*']
        ],
        // MAINTENANCE
        [
            'header'    => 'maintenance',
            'can'       => [
                'department access',
                'SO cut-off access',
                'company access',
                'discount access',
                'account access',
                'branch access',
                'invoice term access',
                'product access',
                'price code access',
                'sales people access',
                'operation process access',
            ]
        ],
        [
            'text'      => 'Departments',
            'url'       => '/department',
            'icon'      => 'fas fa-fw fa-layer-group',
            'can'       => 'department access',
            'active'    => ['department*']
        ],
        [
            'text'      => 'SO Cut-offs',
            'url'       => '/cut-off',
            'icon'      => 'fas fa-fw fa-clock',
            'can'       => 'so cut-off access',
            'active'    => ['cut-off*']
        ],
        [
            'text'      => 'Companies',
            'url'       => '/company',
            'icon'      => 'fas fa-fw fa-building',
            'can'       => 'company access',
            'active'    => ['company*']
        ],
        [
            'text'      => 'Discounts',
            'url'       => '/discount',
            'icon'      => 'fas fa-fw fa-tag',
            'can'       => 'discount access',
            'active'    => ['discount*']
        ],
        [
            'text'      => 'Invoice Terms',
            'url'       => '/invoice-term',
            'icon'      => 'fas fa-fw fa-asterisk',
            'can'       => 'invoice term access',
            'active'    => ['invoice-term*']
        ],
        [
           'text'      => 'Brands',
            'url'       => '/brand',
            'icon'      => 'fas fa-fw fa-copyright',
            'can'       => 'brand access',
            'active'    => ['brand*']
        ],
        [
            'text'      => 'Products',
            'url'       => '/product',
            'icon'      => 'fas fa-fw fa-box',
            'can'       => 'product access',
            'active'    => ['product', 'product/create', 'product/*']
        ],
        [
            'text'      => 'Price Codes',
            'url'       => '/price-code',
            'icon'      => 'fas fa-fw fa-money-bill',
            'can'       => 'price code access',
            'active'    => ['price-code*']
        ],
        [
            'text'      => 'Accounts',
            'url'       => '/account',
            'icon'      => 'fas fa-fw fa-file-invoice',
            'can'       => 'account access',
            'active'    => ['account*', 'shipping-address*']
        ],
        [
            'text'      => 'Account Reference',
            'url'       => '/reference-account',
            'icon'      => 'fas fa-fw fa-hashtag',
            'can'       => 'account reference access',
            'active'    => ['reference-account*']
        ],
        [
            'text'      => 'Ship Address Mapping',
            'url'       => '/ship-address-mapping',
            'icon'      => 'fas fa-fw fa-map-signs',
            'can'       => 'ship address mapping access',
            'active'    => ['ship-address-mapping*']
        ],
        [
            'text'      => 'Branches',
            'url'       => '/branch',
            'icon'      => 'fas fa-fw fa-code-branch',
            'can'       => 'branch access',
            'active'    => ['branch*']
        ],
        [
            'text'      => 'Regions',
            'url'       => '/region',
            'icon'      => 'fas fa-fw fa-map',
            'can'       => 'region access',
            'active'    => ['region*']
        ],
        [
            'text'      => 'Classifications',
            'url'       => '/classification',
            'icon'      => 'fas fa-fw fa-store',
            'can'       => 'classification access',
            'active'    => ['classification*']
        ],
        [
            'text'      => 'Areas',
            'url'       => '/area',
            'icon'      => 'fas fa-fw fa-globe',
            'can'       => 'area access',
            'active'    => ['area*']
        ],
        [
            'text'      => 'Sales People',
            'url'       => '/sales-people',
            'icon'      => 'fas fa-fw fa-universal-access',
            'can'       => 'sales people access',
            'active'    => ['sales-people*']
        ],
        [
            'text'      => 'Operation Processes',
            'url'       => '/operation-process',
            'icon'      => 'fas fa-fw fa-microchip',
            'can'       => 'operation process access',
            'active'    => ['operation-process*']
        ],
        [
            'text'      => 'Cost Centers',
            'url'       => '/cost-center',
            'icon'      => 'fas fa-fw fa-user-cog',
            'can'       => 'cost center access',
            'active'    => ['cost-center*']
        ],
        [
            'text'      => 'Holidays',
            'url'       => '/holiday',
            'icon'      => 'fas fa-fw fa-calendar-day',
            'can'       => 'holiday access',
            'active'    => ['holiday*']
        ],
        [
            'header'    => 'system_menu',
            'can'       => [
                'user access',
                'role access',
                'settings access',
                'account login access'
            ]
        ],
        [
            'text'      => 'Account Logins',
            'url'       => '/login-account',
            'icon'      => 'fas fa-fw fa-user-clock',
            'can'       => 'account login access',
            'active'    => ['login-account*']
        ],
        [
            'text'      => 'Users',
            'url'       => '/user',
            'icon'      => 'fas fa-fw fa-users',
            'can'       => 'user access',
            'active'    => ['user*']
        ],
        [
            'text'      => 'Organization Structure',
            'url'       => '/organizational-structure',
            'icon'      => 'fas fa-fw fa-sitemap',
            'can'       => 'organizational structure access',
            'active'    => ['organizational-structure*']
        ],
        [
            'text'      => 'Upload Templates',
            'url'       => '/upload-template',
            'icon'      => 'fas fa-fw fa-sitemap',
            'active'    => ['upload-template*']
        ],
        [
            'text'      => 'Roles',
            'url'       => '/role',
            'icon'      => 'fas fa-fw fa-user-tag',
            'can'       => 'role access',
            'active'    => ['role*']
        ],
        [
            'text'      => 'System Logs',
            'url'       => '/system-logs',
            'icon'      => 'fas fa-fw fa-business-time',
            'can'       => 'system logs',
            'active'    => ['system-logs*']
        ],
        [
            'text'      => 'Settings',
            'url'       => '/setting',
            'icon'      => 'fas fa-fw fa-wrench',
            'can'       => 'settings',
            'active'    => ['setting*']
        ],
        [
            'text'      => 'Error Logs',
            'url'       => '/logs',
            'icon'      => 'fas fa-fw fa-bug',
            'can'       => 'role access',
            'active'    => ['logs*']
        ],
        // [
        //     'text'    => 'multilevel',
        //     'icon'    => 'fas fa-fw fa-share',
        //     'submenu' => [
        //         [
        //             'text' => 'level_one',
        //             'url'  => '#',
        //         ],
        //         [
        //             'text'    => 'level_one',
        //             'url'     => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'level_two',
        //                     'url'  => '#',
        //                 ],
        //                 [
        //                     'text'    => 'level_two',
        //                     'url'     => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'level_three',
        //                             'url'  => '#',
        //                         ],
        //                         [
        //                             'text' => 'level_three',
        //                             'url'  => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url'  => '#',
        //         ],
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '/vendor/select2/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '/vendor/select2/css/select2.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'EkkoLightbox' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '/vendor/ekko-lightbox/ekko-lightbox.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '/vendor/ekko-lightbox/ekko-lightbox.min.js',
                ]
            ],
        ],
        'Fullcalendar' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '/vendor/fullcalendar/main.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '/vendor/fullcalendar/main.min.js',
                ]
            ]
        ],
        'bsCustomFileInput' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '/vendor/bs-custom-file-input/bs-custom-file-input.min.js',
                ]
            ]
        ],
        'bsStepper' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => '/vendor/bs-stepper/css/bs-stepper.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '/vendor/bs-stepper/js/bs-stepper.min.js',
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,
];
