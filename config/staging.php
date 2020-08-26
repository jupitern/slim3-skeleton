<?php

return [
    // debug options
    'debug'         => false,    // used for error display
    'consoleOutput' => true,    // display console output
    'baseUrl'       => 'http://localhost:8080/',   // url config. Url must end with a slash '/'
    'indexFile'     => false,
    'slim' => [
        'settings' => [
            'routerCacheFile' => false,
        ],
    ],
    'services' => [

    ],
];
