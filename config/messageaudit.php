<?php

return [
    'corp_id' => env('WXWORK_CORP_ID'),
    'secret' => env('WXWORK_SECRET'),
    'private_key' => env('WXWORK_PRIVATE_KEY'),
    'data_limit' => env('WXWORK_DATA_LIMIT', 100),
];
