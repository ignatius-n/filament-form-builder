<?php

/**
 * Template: Change request.
 */
return [
    'name' => 'Change request',
    'category' => 'Agency & freelance',
    'description' => 'Scope changes with client sign-off.',
    'form' => [
        'name' => 'Change request',
        'fields' => [
            [
                'type' => 'text',
                'data' => [
                    'label' => 'Project',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'label' => 'Requested by',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'richtext',
                'data' => [
                    'label' => 'Describe the change',
                    'required' => true,
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Urgency',
                    'choices' => [
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'urgent' => 'Urgent',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'date',
                'data' => [
                    'label' => 'Needed by',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'consent',
                'data' => [
                    'label' => 'I understand this may affect the timeline and the budget',
                    'required' => true,
                ],
            ],
        ],
    ],
];
