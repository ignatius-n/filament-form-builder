<?php

/**
 * Template: Return request.
 */
return [
    'name' => 'Return request',
    'category' => 'Support',
    'description' => 'Returns with reasons and order details.',
    'form' => [
        'name' => 'Return request',
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
                    'label' => 'Order number',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'date',
                'data' => [
                    'label' => 'Order date',
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Reason',
                    'key' => 'reason',
                    'choices' => [
                        'damaged' => 'Arrived damaged',
                        'wrong' => 'Wrong item',
                        'unwanted' => 'No longer needed',
                        'other' => 'Other',
                    ],
                    'required' => true,
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Details',
                    'rows' => 3,
                    'visibility' => 'when',
                    'visibility_rules' => [
                        [
                            'field' => 'reason',
                            'operator' => 'equals',
                            'value' => 'other',
                        ],
                    ],
                    'required' => true,
                    'requirement' => 'when',
                    'requirement_rules' => [
                        [
                            'field' => 'reason',
                            'operator' => 'equals',
                            'value' => 'other',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'file',
                'data' => [
                    'label' => 'Photos',
                    'multiple' => true,
                    'max_files' => 5,
                    'accept' => 'png, jpg',
                    'max_kb' => 4096,
                ],
            ],
        ],
    ],
];
