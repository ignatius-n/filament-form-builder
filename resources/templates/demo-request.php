<?php

/**
 * Template: Demo request.
 */
return [
    'name' => 'Demo request',
    'category' => 'Lead generation',
    'description' => 'Qualify demo requests before the call.',
    'form' => [
        'name' => 'Demo request',
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
                'type' => 'text',
                'data' => [
                    'label' => 'Job title',
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Team size',
                    'choices' => [
                        '1-5' => '1–5',
                        '6-20' => '6–20',
                        '21-100' => '21–100',
                        '100+' => '100+',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'multiselect',
                'data' => [
                    'label' => 'Interested in',
                    'choices' => [
                        'forms' => 'Forms',
                        'automation' => 'Automation',
                        'analytics' => 'Analytics',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'datetime',
                'data' => [
                    'label' => 'Preferred time',
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Anything we should know?',
                    'rows' => 3,
                ],
            ],
        ],
    ],
];
