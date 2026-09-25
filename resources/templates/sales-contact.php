<?php

/**
 * Template: Sales contact.
 */
return [
    'name' => 'Sales contact',
    'category' => 'Contact',
    'description' => 'Collect company and budget details from leads.',
    'form' => [
        'name' => 'Sales contact',
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
                'type' => 'text',
                'data' => [
                    'label' => 'Company',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'url',
                'data' => [
                    'label' => 'Website',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Company size',
                    'choices' => [
                        '1-10' => '1–10',
                        '11-50' => '11–50',
                        '51-200' => '51–200',
                        '200+' => '200+',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Budget',
                    'choices' => [
                        'lt5k' => 'Under 5,000',
                        '5k-20k' => '5,000–20,000',
                        'gt20k' => 'Over 20,000',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What are you looking for?',
                    'rows' => 4,
                    'required' => true,
                ],
            ],
        ],
    ],
];
