<?php

/**
 * Template: Event registration.
 */
return [
    'name' => 'Event registration',
    'category' => 'Orders & booking',
    'description' => 'Multi-step registration with ticket and attendance choices.',
    'form' => [
        'name' => 'Event registration',
        'settings' => [
            'mode' => 'wizard',
        ],
        'fields' => [
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Attendee',
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
                            'type' => 'phone',
                            'data' => [
                                'label' => 'Phone',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'label' => 'Company',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'label' => 'Job title',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Ticket and attendance',
                    'fields' => [
                        [
                            'type' => 'radio',
                            'data' => [
                                'label' => 'Attendance',
                                'key' => 'attendance',
                                'choices' => [
                                    'in_person' => 'In person',
                                    'virtual' => 'Virtual',
                                ],
                                'required' => true,
                            ],
                        ],
                        [
                            'type' => 'select',
                            'data' => [
                                'label' => 'T-shirt size',
                                'key' => 'tshirt',
                                'choices' => [
                                    's' => 'S',
                                    'm' => 'M',
                                    'l' => 'L',
                                    'xl' => 'XL',
                                ],
                                'visibility' => 'when',
                                'visibility_rules' => [
                                    [
                                        'field' => 'attendance',
                                        'operator' => 'equals',
                                        'value' => 'in_person',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'checkboxes',
                            'data' => [
                                'label' => 'Sessions',
                                'choices' => [
                                    'keynote' => 'Keynote',
                                    'workshop' => 'Workshop',
                                    'networking' => 'Networking',
                                ],
                            ],
                        ],
                        [
                            'type' => 'textarea',
                            'data' => [
                                'label' => 'Dietary requirements',
                                'rows' => 2,
                                'visibility' => 'when',
                                'visibility_rules' => [
                                    [
                                        'field' => 'attendance',
                                        'operator' => 'equals',
                                        'value' => 'in_person',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Confirm',
                    'fields' => [
                        [
                            'type' => 'consent',
                            'data' => [
                                'label' => 'I accept the',
                                'link_text' => 'event terms',
                                'link_url' => '/terms',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
