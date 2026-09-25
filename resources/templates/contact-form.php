<?php

/**
 * Template: Contact form.
 */
return [
    'name' => 'Contact form',
    'category' => 'Contact',
    'description' => 'Let visitors reach you without showing an email address.',
    'form' => [
        'name' => 'Contact form',
        'success_message' => 'Thanks, we will get back to you shortly.',
        'fields' => [
            [
                'type' => 'text',
                'data' => [
                    'label' => 'Full name',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'email',
                'data' => [
                    'label' => 'Email',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Topic',
                    'choices' => [
                        'sales' => 'Sales',
                        'support' => 'Support',
                        'other' => 'Other',
                    ],
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Message',
                    'rows' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'consent',
                'data' => [
                    'label' => 'I agree to the',
                    'link_text' => 'privacy policy',
                    'link_url' => '/privacy',
                    'required' => true,
                ],
            ],
        ],
    ],
];
