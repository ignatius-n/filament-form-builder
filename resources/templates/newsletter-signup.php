<?php

/**
 * Template: Newsletter signup.
 */
return [
    'name' => 'Newsletter signup',
    'category' => 'Lead generation',
    'description' => 'A single email field with a consent checkbox.',
    'form' => [
        'name' => 'Newsletter signup',
        'submit_label' => 'Subscribe',
        'success_message' => 'You are on the list. Check your inbox to confirm.',
        'fields' => [
            [
                'type' => 'email',
                'data' => [
                    'label' => 'Email',
                    'required' => true,
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'label' => 'First name',
                ],
            ],
            [
                'type' => 'consent',
                'data' => [
                    'label' => 'I want to receive the newsletter and agree to the',
                    'link_text' => 'privacy policy',
                    'link_url' => '/privacy',
                    'required' => true,
                ],
            ],
        ],
    ],
];
