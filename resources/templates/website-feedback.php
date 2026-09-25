<?php

/**
 * Template: Website feedback.
 */
return [
    'name' => 'Website feedback',
    'category' => 'Feedback & surveys',
    'description' => 'Let visitors report a broken page.',
    'form' => [
        'name' => 'Website feedback',
        'fields' => [
            [
                'type' => 'url',
                'data' => [
                    'label' => 'Page URL',
                    'required' => true,
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'What happened?',
                    'choices' => [
                        'broken' => 'Something is broken',
                        'confusing' => 'Something is confusing',
                        'idea' => 'I have an idea',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Tell us more',
                    'rows' => 4,
                    'required' => true,
                ],
            ],
            [
                'type' => 'file',
                'data' => [
                    'label' => 'Screenshot',
                    'accept' => 'png, jpg',
                    'max_kb' => 4096,
                ],
            ],
            [
                'type' => 'hidden',
                'data' => [
                    'label' => 'Referrer',
                    'key' => 'referrer',
                ],
            ],
        ],
    ],
];
