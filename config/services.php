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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'external_login' => [
        'url' => env('EXTERNAL_LOGIN_API_URL', 'https://salary.cpn1.go.th/api/external/login'),
        'key' => env('EXTERNAL_LOGIN_API_KEY', 'ea940c1b1968568de50720ceda9084abd8d66e259664fa5631811a982fc72532'),
        'timeout' => (int) env('EXTERNAL_LOGIN_API_TIMEOUT', 10),
    ],

    'school_distance' => [
        'base_url' => env('SCHOOL_DISTANCE_API_URL', 'https://router.project-osrm.org'),
        'profile' => env('SCHOOL_DISTANCE_API_PROFILE', 'driving'),
        'timeout' => (int) env('SCHOOL_DISTANCE_API_TIMEOUT', 15),
        'office_lat' => env('SCHOOL_DISTANCE_OFFICE_LAT', '10.4909332'),
        'office_lng' => env('SCHOOL_DISTANCE_OFFICE_LNG', '99.1248801'),
        'max_distance_km' => (float) env('SCHOOL_DISTANCE_MAX_KM', 200),
    ],

    'obec_safety' => [
        'register_url' => env('OBEC_SAFETY_REGISTER_URL', 'https://safetycenter.obec.go.th/osds/register'),
        'school_search_url' => env('OBEC_SAFETY_SCHOOL_SEARCH_URL', 'https://safetycenter.obec.go.th/osds/schoolSearch'),
        'area_code' => env('OBEC_SAFETY_AREA_CODE', '1086010000'),
        'timeout' => (int) env('OBEC_SAFETY_TIMEOUT', 20),
    ],

    'hrms_opendata' => [
        'base_url' => env('HRMS_OPENDATA_BASE_URL', 'https://hrms.obec.go.th/api/opendata'),
        'timeout' => (int) env('HRMS_OPENDATA_TIMEOUT', 20),
        'cache_minutes' => (int) env('HRMS_OPENDATA_CACHE_MINUTES', 15),
    ],

    'hrms_onet' => [
        'base_url' => env('HRMS_ONET_BASE_URL', 'https://hrms.obec.go.th/api'),
        'timeout' => (int) env('HRMS_ONET_TIMEOUT', 20),
        'cache_minutes' => (int) env('HRMS_ONET_CACHE_MINUTES', 30),
    ],

];
