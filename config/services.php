<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'firebase' => [
        'database_url' => 'https://stage-connect-v2-default-rtdb.firebaseio.com',
        'project_id' => 'stage-connect-v2',
        'private_key_id' =>'dbb45542098051fd5edf39329f46634d6b47d05e', 
        
        'private_key' => '-----BEGIN PRIVATE KEY-----\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCGIrL+ID61vCVg\n3ldTkizLEsFE5ehyWxoU7EfxZ0qNGXiytjJ9/LJjZRTwA0lWJ6o2uz0lq+h2CBaL\n5lAfWn8Mw4JjsfHvK3NNrF30Cc1R6p0nyhJHFrlvtgnGVoPKYvhbGaCYgB3UnL5R\nDcLFocQB9zqoJZwVU3HW6f9QnFwWNYqBHcQ+lp0J53NEUoZkgPOZ7pC/trE+m8/M\nuThdYKZJP56n/mNEiqiCicwlPDhLx1AnICwvluoPXgdutTngrHs1RQ3RRrOH5Ssq\ntEkyWHKwCtEr9hu0sw34YyYwI1JFUNBA1nb6s2feoeqkvFoLcDvRwbrfeh6wE07b\nvmsTILoRAgMBAAECggEABvb3hgow02Eh7DgTPvW+sWsngZ8x7PklvysfF63iMTYe\nviHHeKL1tMM/fXBgPxLXa9jYRZPZ4cINAKXcaGpboTMoGqrXSfC+v4xZAwY8xNl6\noEGl5g7MIVypgITGukweAuXvYKe7VMowOBdvsvERszvt7ePpCTLwXOVAwmPiBK2f\nBD5+8FFfHQItFRdYNwA32dwyn3/bUhfn6T9OU0/QERIomy+gMSJjN+RQ28DwdSl6\nM3h4k/6CIr+GXqQMNHks1vC7Fv+1jMoCpmbLQImuqVNov68P6+O6yS/jHHem3ezB\na+JvIkt+froZOJZX9jRdzgJvzdMBLcTrE7uMgkqzmQKBgQC9b2jDz+Q+WgZAzwJ9\noH2ThsnZ2gVekeZcJJC9FzVz8uQc1bpoOBdTCRFaG0TxGnakPY0oprpU7ui/HimD\nM8+GQs1kukkRK1ekNsQkRZSFCBLy+u51Hhmxh/zSrJMcN7yqcXm6sO02ka6T8n2K\nYjebmbd/Pkm9NGWeVM/1uZIAuQKBgQC1RNNCQRo/46A6jbr9LrHwpKKy1rkre7nh\ncbs4+k6jq+MRjS++UWvoE+W3voGSz3uU/80k/vn4FSbuMeBxslrEBRKAXQk8H5fA\n8YQe2AaFQ4e7M8ynFx534FH91lp/56JvKwNl2/wEWj/5Z+dCC53nex42FKAziMez\n4d3Ea2/oGQKBgBMlj5E7Efa7YqZqRfYsooSmuwAp6SEF3iKyD28NSXnOsv+3GoAu\nKy1ZXUeeOfmkkxlGn13cfDjZbOzkP43jTrZjSh9ENN5nYxRV9L01yy0nA8NLWY68\n3Bv1grhIeRYBDDCuB3+3IkLBtsN9XvL9umEfyEw0H3/gK+U07VAI0ZkBAoGAdaOp\nBwKBKp1rSxTYGZ/IU0hFXpMDsTimtay9uIiZqJbQ0n8yUO0+fmsLVmgIZ2LH7Tbl\nx/DCAUKY9VThOGhrh3NTQoMejyphrhIPkITaFnpxCAjh9D6Iw3uNOpN2QDPvsv3T\nQrO1po17nzzTVIV9gNDiFm7kWQ2XrSIUbnjbM2ECgYAi7k6kanjFRAkcquh9hZWw\nPAVWIP2EMhbyjNE+CS91gfa1ti1PZnUZOBpeFH3Eoim45DST3KLZsRDmYvLolo5e\nuRpxALgdBgWo0Ys7rCqv1JWbgy4rh9ZLLuuRVgV0LxJKY3wcRWoXc7ao+ZW7MaYH\nrrkBOqIsJGaPfLJDKwtF0w==\n-----END PRIVATE KEY-----\n',
        'client_email' => 'firebase-adminsdk-fbsvc@stage-connect-v2.iam.gserviceaccount.com',
        'client_id' => '105946584410254845898',
        'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40stage-connect-v2.iam.gserviceaccount.com',
        'credentials' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase_credentials.json')),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

];
