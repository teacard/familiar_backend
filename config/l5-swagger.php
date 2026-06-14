<?php

return [
    'default' => 'admin-api',
    'documentations' => [
        // 前台 API 尚未開發，暫時註解停用
        // 'default' => [
        //     'api' => ['title' => '前台API'],
        //     'routes' => ['api' => 'api/swagger', 'docs' => 'api/docs'],
        //     'paths' => [
        //         'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
        //         'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
        //         'docs_json' => 'api-docs.json',
        //         'docs_yaml' => 'api-docs.yaml',
        //         'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
        //         'annotations' => [base_path('app/Docs/All'), base_path('app/Docs/Default')],
        //     ],
        // ],

        'admin-api' => [
            'api' => [
                'title' => '後台API',
            ],

            'routes' => [
                'api' => 'admin-api/swagger',
                'docs' => 'admin-api/docs',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),
                'docs_json' => 'admin-api-docs.json',
                'docs_yaml' => 'admin-api-docs.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),
                'annotations' => [
                    base_path('app/Docs/All'),
                    base_path('app/Docs/AdminApi'),
                ],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            /*
             * Route for accessing parsed swagger annotations.
             * 存取已解析 Swagger 註解（原始文件資料）的路由路徑
             */
            'docs' => 'docs',

            /*
             * Route for Oauth2 authentication callback.
             * OAuth2 認證流程完成後的回呼路由路徑
             */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * Middleware allows to prevent unexpected access to API documentation
             * 為各路由設定中介層，防止未授權存取 API 文件
             */
            'middleware' => [
                'api' => [],
                'asset' => [],
                'docs' => [],
                'oauth2_callback' => [],
            ],

            /*
             * Route Group options
             * 路由群組的額外選項設定（例如前綴、命名空間等）
             */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Absolute path to location where parsed annotations will be stored
             * 解析後的 API 文件（如 api-docs.json）存放的絕對路徑
             */
            'docs' => storage_path('api-docs'),

            /*
             * Absolute path to directory where to export views
             * Swagger UI 視圖檔案的匯出目錄絕對路徑
             */
            'views' => base_path('resources/views/vendor/l5-swagger'),

            /*
             * Edit to set the api's base path
             * 設定 API 的基礎路徑，會反映在 Swagger 文件的 servers 區塊中
             */
            'base' => env('L5_SWAGGER_BASE_PATH', null),

            /*
             * Absolute path to directories that should be excluded from scanning
             * @deprecated Please use `scanOptions.exclude`
             * `scanOptions.exclude` overwrites this
             *
             * 掃描時要排除的目錄絕對路徑（已棄用，請改用 scanOptions.exclude）
             * 若同時設定 scanOptions.exclude，則以該設定為優先
             */
            'excludes' => [],
        ],

        'scanOptions' => [
            /**
             * Optional CustomGeneratorInterface implementation that creates an OpenApi\Generator instance.
             * Use this to provide a custom pre-configured generator.
             * Accepts an instance or a class name (FQCN) implementing the interface.
             *
             * 可選的自訂 Generator 工廠類別，用於建立 OpenApi\Generator 實例。
             * 若需要使用預先配置好的自訂 Generator，可在此指定實作 CustomGeneratorInterface 的類別名稱或實例。
             *
             * @see \L5Swagger\CustomGeneratorInterface
             */
            'generator_factory' => null,

            /**
             * Configuration for default processors. Allows to pass processors configuration to swagger-php.
             *
             * 預設處理器的設定，可將自訂配置傳遞給 swagger-php 的各處理器。
             *
             * @link https://zircote.github.io/swagger-php/reference/processors.html
             */
            'default_processors_configuration' => [
                /** Example */
                /**
             * 'operationId.hash' => true,
             * 'pathFilter' => [
             * 'tags' => [
             * '/pets/',
             * '/store/',
             * ],
             * ],.
             */
            ],

            /**
             * analyser: defaults to \OpenApi\StaticAnalyser .
             *
             * 指定程式碼分析器，預設為 \OpenApi\StaticAnalyser。
             *
             * @see \OpenApi\scan
             */
            'analyser' => null,

            /**
             * analysis: defaults to a new \OpenApi\Analysis .
             *
             * 指定分析結果容器，預設會建立一個新的 \OpenApi\Analysis 實例。
             *
             * @see \OpenApi\scan
             */
            'analysis' => null,

            /**
             * Custom processors.
             *
             * Each entry can be:
             * - A class name or instance (inserted after BuildPaths by default)
             * - An array with 'class' and 'after' keys for precise positioning:
             *   ['class' => MyProcessor::class, 'after' => SomeProcessor::class]
             *
             * 自訂處理器清單，可在掃描流程中插入自訂邏輯。
             * 每個項目可以是：
             * - 類別名稱或實例（預設插入在 BuildPaths 之後）
             * - 包含 'class' 與 'after' 鍵的陣列，用於精確指定插入位置
             *
             * @link https://github.com/zircote/swagger-php/tree/master/Examples/processors/schema-query-parameter
             * @see \OpenApi\scan
             */
            'processors' => [
                // \App\SwaggerProcessors\SchemaQueryParameter::class,
                // ['class' => \App\SwaggerProcessors\Custom::class, 'after' => \OpenApi\Processors\AugmentSchemas::class],
            ],

            /**
             * pattern: string       $pattern File pattern(s) to scan (default: *.php) .
             *
             * 指定掃描的檔案比對模式，預設為 *.php（掃描所有 PHP 檔案）。
             *
             * @see \OpenApi\scan
             */
            'pattern' => null,

            /*
             * Absolute path to directories that should be excluded from scanning
             * @note This option overwrites `paths.excludes`
             * @see \OpenApi\scan
             *
             * 掃描時要排除的目錄絕對路徑，此設定會覆蓋 paths.excludes 的設定。
             */
            'exclude' => [],

            /*
             * Allows to generate specs either for OpenAPI 3.0.0 or OpenAPI 3.1.0.
             * By default the spec will be in version 3.0.0
             *
             * 指定產生的 API 規格版本，可選 OpenAPI 3.0.0 或 3.1.0，預設為 3.0.0。
             */
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],

        /*
         * API security definitions. Will be generated into documentation file.
         * API 安全性定義，將會寫入產生的文件檔案中（定義認證方式，如 API Key、OAuth2 等）。
        */
        'securityDefinitions' => [
            'securitySchemes' => [
                /*
                 * Examples of Security schemes
                 * 安全性方案範例（取消註解並依需求調整即可啟用）
                 */
                /*
                'api_key_security_example' => [ // Unique name of security
                    'type' => 'apiKey', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
                    'description' => 'A short description for security scheme',
                    'name' => 'api_key', // The name of the header or query parameter to be used.
                    'in' => 'header', // The location of the API key. Valid values are "query" or "header".
                ],
                'oauth2_security_example' => [ // Unique name of security
                    'type' => 'oauth2', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
                    'description' => 'A short description for oauth2 security scheme.',
                    'flow' => 'implicit', // The flow used by the OAuth2 security scheme. Valid values are "implicit", "password", "application" or "accessCode".
                    'authorizationUrl' => 'http://example.com/auth', // The authorization URL to be used for (implicit/accessCode)
                    //'tokenUrl' => 'http://example.com/auth' // The authorization URL to be used for (password/application/accessCode)
                    'scopes' => [
                        'read:projects' => 'read your projects',
                        'write:projects' => 'modify projects in your account',
                    ]
                ],
                */

                /* Open API 3.0 support
                'passport' => [ // Unique name of security
                    'type' => 'oauth2', // The type of the security scheme. Valid values are "basic", "apiKey" or "oauth2".
                    'description' => 'Laravel passport oauth2 security.',
                    'in' => 'header',
                    'scheme' => 'https',
                    'flows' => [
                        "password" => [
                            "authorizationUrl" => config('app.url') . '/oauth/authorize',
                            "tokenUrl" => config('app.url') . '/oauth/token',
                            "refreshUrl" => config('app.url') . '/token/refresh',
                            "scopes" => []
                        ],
                    ],
                ],
                'sanctum' => [ // Unique name of security
                    'type' => 'apiKey', // Valid values are "basic", "apiKey" or "oauth2".
                    'description' => 'Enter token in format (Bearer <token>)',
                    'name' => 'Authorization', // The name of the header or query parameter to be used.
                    'in' => 'header', // The location of the API key. Valid values are "query" or "header".
                ],
                */],
            'security' => [
                /*
                 * Examples of Securities
                 * 安全性套用範例（指定哪些端點預設要求認證）
                 */
                [
                    /*
                    'oauth2_security_example' => [
                        'read',
                        'write'
                    ],

                    'passport' => []
                    */],
            ],
        ],

        /*
         * Set this to `true` in development mode so that docs would be regenerated on each request
         * Set this to `false` to disable swagger generation on production
         *
         * 開發環境建議設為 true，每次請求時自動重新產生 API 文件，方便即時預覽變更。
         * 正式環境應設為 false，避免每次請求都重新掃描，影響效能。
         */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),

        /*
         * Set this to `true` to generate a copy of documentation in yaml format
         *
         * 設為 true 時，會同時產生一份 YAML 格式的 API 文件副本。
         */
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        /*
         * Edit to trust the proxy's ip address - needed for AWS Load Balancer
         * string[]
         *
         * 設定信任的代理 IP 位址，在使用 AWS Load Balancer 等反向代理時需要設定此項，
         * 確保 Swagger 正確辨識客戶端的真實 IP 與協定。
         */
        'proxy' => false,

        /*
         * Configs plugin allows to fetch external configs instead of passing them to SwaggerUIBundle.
         * See more at: https://github.com/swagger-api/swagger-ui#configs-plugin
         *
         * 指定外部設定檔的 URL，讓 Swagger UI 從外部載入設定，而非直接寫入 SwaggerUIBundle。
         * 設為 null 表示停用此功能，使用預設的內嵌設定方式。
         */
        'additional_config_url' => null,

        /*
         * Apply a sort to the operation list of each API. It can be 'alpha' (sort by paths alphanumerically),
         * 'method' (sort by HTTP method).
         * Default is the order returned by the server unchanged.
         *
         * 設定 API 端點清單的排序方式：
         * - 'alpha'：依路徑字母順序排序
         * - 'method'：依 HTTP 方法排序
         * - null（預設）：依伺服器回傳的原始順序顯示
         */
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

        /*
         * Pass the validatorUrl parameter to SwaggerUi init on the JS side.
         * A null value here disables validation.
         *
         * 設定 Swagger UI 的線上驗證器 URL，用於驗證 API 規格的正確性。
         * 設為 null 表示停用線上驗證功能。
         */
        'validator_url' => null,

        /*
         * Swagger UI configuration parameters
         * Swagger UI 介面的顯示與行為設定
         */
        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                /*
                 * Controls the default expansion setting for the operations and tags. It can be :
                 * 'list' (expands only the tags),
                 * 'full' (expands the tags and operations),
                 * 'none' (expands nothing).
                 *
                 * 控制 API 文件的預設展開狀態：
                 * - 'list'：只展開標籤群組，不展開各操作
                 * - 'full'：展開標籤群組與所有 API 操作
                 * - 'none'：全部收合，不展開任何內容
                 */
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                /**
                 * If set, enables filtering. The top bar will show an edit box that
                 * you can use to filter the tagged operations that are shown. Can be
                 * Boolean to enable or disable, or a string, in which case filtering
                 * will be enabled using that string as the filter expression. Filtering
                 * is case-sensitive matching the filter expression anywhere inside
                 * the tag.
                 *
                 * 啟用頂端工具列的搜尋過濾功能，可依標籤名稱篩選顯示的 API 操作。
                 * 設為 true 啟用（空白搜尋框）、false 停用，
                 * 或傳入字串作為預設過濾條件（區分大小寫）。
                 */
                'filter' => env('L5_SWAGGER_UI_FILTERS', true), // true | false
            ],

            'authorization' => [
                /*
                 * If set to true, it persists authorization data, and it would not be lost on browser close/refresh
                 *
                 * 設為 true 時，在 Swagger UI 中輸入的認證資訊（如 Bearer Token）會持久保存，
                 * 關閉瀏覽器或重新整理頁面後仍然有效。
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),

                'oauth2' => [
                    /*
                     * If set to true, adds PKCE to AuthorizationCodeGrant flow
                     *
                     * 設為 true 時，在 OAuth2 授權碼流程中啟用 PKCE（Proof Key for Code Exchange）
                     * 機制，提升公開用戶端的安全性。
                     */
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        /*
         * Constants which can be used in annotations
         * 可在 Swagger 註解中使用的常數定義，方便統一管理如 API Host 等重複使用的值
         */
        'constants' => [
            'L5_SWAGGER_API_URL' => env('APP_URL', 'http://127.0.0.1:8000') . '/api',
            'L5_SWAGGER_ADMIN_API_URL' => env('APP_URL', 'http://127.0.0.1:8000') . '/admin-api',
        ],
    ],
];
