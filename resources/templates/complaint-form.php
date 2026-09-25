<?php

/**
 * Template: Complaint form.
 */
return [
    'name' => 'Complaint form',
    'category' => 'Support',
    'description' => 'Customer complaints with a severity and an order reference.',
    'form' => [
        'name' => 'Complaint form',
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
                    'label' => 'Order or account number',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'date',
                'data' => [
                    'label' => 'Date of the incident',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Severity',
                    'choices' => [
                        'minor' => 'Minor',
                        'moderate' => 'Moderate',
                        'serious' => 'Serious',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What happened?',
                    'rows' => 6,
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What would resolve it for you?',
                    'rows' => 3,
                ],
            ],
        ],
    ],
];
