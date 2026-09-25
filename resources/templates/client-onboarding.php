<?php

/**
 * Template: Client onboarding.
 */
return [
    'name' => 'Client onboarding',
    'category' => 'Agency & freelance',
    'description' => 'Gather what you need to start a project.',
    'form' => [
        'name' => 'Client onboarding',
        'settings' => [
            'mode' => 'wizard',
        ],
        'fields' => [
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Company',
                    'fields' => [
                        [
                            'type' => 'text',
                            'data' => [
                                'label' => 'Company name',
                                'required' => true,
                            ],
                        ],
                        [
                            'type' => 'url',
                            'data' => [
                                'label' => 'Website',
                            ],
                        ],
                        [
                            'type' => 'text',
                            'data' => [
                                'label' => 'Main contact',
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
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Project',
                    'fields' => [
                        [
                            'type' => 'multiselect',
                            'data' => [
                                'label' => 'Services',
                                'choices' => [
                                    'design' => 'Design',
                                    'development' => 'Development',
                                    'seo' => 'SEO',
                                    'content' => 'Content',
                                ],
                                'required' => true,
                            ],
                        ],
                        [
                            'type' => 'currency',
                            'data' => [
                                'label' => 'Budget',
                                'prefix' => '€',
                                'decimals' => 0,
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'date',
                            'data' => [
                                'label' => 'Target launch',
                                'width' => 'half',
                            ],
                        ],
                        [
                            'type' => 'richtext',
                            'data' => [
                                'label' => 'Goals',
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'section',
                'data' => [
                    'label' => 'Access',
                    'fields' => [
                        [
                            'type' => 'textarea',
                            'data' => [
                                'label' => 'Where do we find your brand assets?',
                                'rows' => 2,
                            ],
                        ],
                        [
                            'type' => 'toggle',
                            'data' => [
                                'label' => 'You will provide hosting access',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
