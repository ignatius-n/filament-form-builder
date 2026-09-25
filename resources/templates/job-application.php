<?php

/**
 * Template: Job application.
 */
return [
    'name' => 'Job application',
    'category' => 'HR & recruitment',
    'description' => 'CV upload, availability and a cover note.',
    'form' => [
        'name' => 'Job application',
        'settings' => [
            'mode' => 'wizard',
        ],
        'fields' => [
            [
                'type' => 'section',
                'data' => [
                    'label' => 'About you',
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
                                'required' => true,
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'country',
                            'data' => [
                                'label' => 'Country',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'url',
                            'data' => [
                                'label' => 'LinkedIn or portfolio',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'The role',
                    'fields' => [
                        [
                            'type' => 'select',
                            'data' => [
                                'label' => 'Position',
                                'choices' => [
                                    'developer' => 'Developer',
                                    'designer' => 'Designer',
                                    'support' => 'Support',
                                ],
                                'required' => true,
                            ],
                        ],
                        [
                            'type' => 'date',
                            'data' => [
                                'label' => 'Available from',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'currency',
                            'data' => [
                                'label' => 'Expected salary',
                                'prefix' => '€',
                                'decimals' => 0,
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'file',
                            'data' => [
                                'label' => 'CV',
                                'accept' => 'pdf, doc, docx',
                                'max_kb' => 8192,
                                'required' => true,
                            ],
                        ],
                        [
                            'type' => 'textarea',
                            'data' => [
                                'label' => 'Cover note',
                                'rows' => 6,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Consent',
                    'fields' => [
                        [
                            'type' => 'consent',
                            'data' => [
                                'label' => 'I agree to the processing of my application as described in the',
                                'link_text' => 'privacy notice',
                                'link_url' => '/privacy',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
