<?php

/**
 * Template: Product feedback.
 */
return [
    'name' => 'Product feedback',
    'category' => 'Feedback & surveys',
    'description' => 'Capture feature requests and priorities.',
    'form' => [
        'name' => 'Product feedback',
        'fields' => [
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Type',
                    'choices' => [
                        'bug' => 'Bug',
                        'feature' => 'Feature request',
                        'other' => 'Other',
                    ],
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Priority',
                    'choices' => [
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'label' => 'Summary',
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Details',
                    'rows' => 6,
                    'required' => true,
                ],
            ],
            [
                'type' => 'file',
                'data' => [
                    'label' => 'Screenshot',
                    'accept' => 'png, jpg, gif',
                    'max_kb' => 4096,
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
