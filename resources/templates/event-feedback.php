<?php

/**
 * Template: Event feedback.
 */
return [
    'name' => 'Event feedback',
    'category' => 'Feedback & surveys',
    'description' => 'Ask attendees right after the event.',
    'form' => [
        'name' => 'Event feedback',
        'fields' => [
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'Overall rating',
                    'max' => 5,
                    'required' => true,
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'Speakers',
                    'max' => 5,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'rating',
                'data' => [
                    'label' => 'Venue',
                    'max' => 5,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'toggle_buttons',
                'data' => [
                    'label' => 'Would you attend again?',
                    'choices' => [
                        'yes' => 'Yes',
                        'maybe' => 'Maybe',
                        'no' => 'No',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What did you like most?',
                    'rows' => 3,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'What could be better?',
                    'rows' => 3,
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
