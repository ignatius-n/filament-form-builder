<?php

/**
 * Template: Exit interview.
 */
return [
    'name' => 'Exit interview',
    'category' => 'HR & recruitment',
    'description' => 'Hear from people leaving the company.',
    'form' => [
        'name' => 'Exit interview',
        'fields' => [
            [
                'type' => 'text',
                'data' => [
                    'label' => 'Name',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'date',
                'data' => [
                    'label' => 'Last day',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Main reason for leaving',
                    'choices' => [
                        'career' => 'Career growth',
                        'compensation' => 'Compensation',
                        'management' => 'Management',
                        'relocation' => 'Relocation',
                        'other' => 'Other',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'How would you rate your experience?',
                    'max' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What could we have done differently?',
                    'rows' => 4,
                ],
            ],
            [
                'type' => 'toggle',
                'data' => [
                    'label' => 'Would you consider coming back?',
                ],
            ],
        ],
    ],
];
