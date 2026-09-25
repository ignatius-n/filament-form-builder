<?php

/**
 * Template: Employee engagement survey.
 */
return [
    'name' => 'Employee engagement survey',
    'category' => 'Feedback & surveys',
    'description' => 'Anonymous by default.',
    'form' => [
        'name' => 'Employee engagement survey',
        'store_submissions' => true,
        'fields' => [
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Department',
                    'choices' => [
                        'engineering' => 'Engineering',
                        'sales' => 'Sales',
                        'support' => 'Support',
                        'operations' => 'Operations',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'I feel valued at work',
                    'max' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'I have what I need to do my job',
                    'max' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'I would recommend this company as a place to work',
                    'max' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What would make your work better?',
                    'rows' => 4,
                ],
            ],
        ],
    ],
];
