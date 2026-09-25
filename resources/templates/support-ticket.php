<?php

/**
 * Template: Support ticket.
 */
return [
    'name' => 'Support ticket',
    'category' => 'Support',
    'description' => 'Structured problem reports with severity.',
    'form' => [
        'name' => 'Support ticket',
        'success_message' => 'Ticket received. We reply within one business day.',
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
                    'label' => 'Subject',
                    'required' => true,
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Severity',
                    'choices' => [
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ],
                    'required' => true,
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'select',
                'data' => [
                    'label' => 'Product area',
                    'choices' => [
                        'account' => 'Account',
                        'billing' => 'Billing',
                        'app' => 'Application',
                    ],
                    'width' => 'half',
                ],
            ],
            [
                'type' => 'textarea',
                'data' => [
                    'label' => 'Describe the problem',
                    'rows' => 6,
                    'required' => true,
                ],
            ],
            [
                'type' => 'file',
                'data' => [
                    'label' => 'Attachments',
                    'multiple' => true,
                    'max_files' => 3,
                    'max_kb' => 8192,
                ],
            ],
        ],
    ],
];
