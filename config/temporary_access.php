<?php

return [

    /*
     * The token generator option allows you to decide
     * which generator implementation to be used when
     * generating new token.
     *
     * Here are the options:
     *  - string
     *  - numeric
     *  - numeric-no-0
     */

    'token_generator' => 'string',

    /*
     * The name of the table to be used to store
     * the temporary access tokens.
     */

    'table'   => 'temporary_access_tokens',

    /*
     * The expiry time of the tokens in minutes.
     */

    'expires' => 15, // in minutes.
];
