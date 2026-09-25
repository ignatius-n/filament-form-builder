<?php

/**
 * Template: Customer satisfaction survey.
 */
return [
    'name' => 'Customer satisfaction survey',
    'category' => 'Feedback & surveys',
    'description' => 'A short NPS-style survey.',
    'form' => [
        'name' => 'Customer satisfaction survey',
        'fields' => [
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'How likely are you to recommend us?',
                    'key' => 'nps',
                    'max' => 10,
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What is the main reason for your score?',
                    'rows' => 3,
                ],
            ],
            [
                'type' => 'checkboxes',
                'data' => [
                    'label' => 'Which features do you use?',
                    'choices' => [
                        'forms' => 'Forms',
                        'reports' => 'Reports',
                        'api' => 'API',
                    ],
                ],
            ],
            [
                'type' => 'email',
                'data' => [
                    'label' => 'Email (optional)',
                ],
            ],
        ],
    ],
];
