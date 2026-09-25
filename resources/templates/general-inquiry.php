<?php

/**
 * Template: General inquiry.
 */
return [
    'name' => 'General inquiry',
    'category' => 'Contact',
    'description' => 'Route questions by category.',
    'form' => [
        'name' => 'General inquiry',
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
                'type' => 'phone',
                'data' => [
                    'label' => 'Phone',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Category',
                    'choices' => [
                        'billing' => 'Billing',
                        'product' => 'Product',
                        'partnership' => 'Partnership',
                        'press' => 'Press',
                    ],
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'How can we help?',
                    'rows' => 5,
                    'required' => true,
                ],
            ],
        ],
    ],
];
