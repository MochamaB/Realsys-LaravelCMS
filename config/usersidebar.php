<?php

return [
    'menu' => [
        [
            'title' => 'Dashboard',
            'icon' => 'ri-dashboard-line',
            'route' => 'dashboard',
            'permission' => null,
        ],
        [
            'title' => 'My Profile',
            'icon' => 'ri-user-settings-line',
            'route' => 'user.profile',
            'permission' => null,
        ],
        [
            'title' => 'Membership',
            'icon' => 'ri-group-line',
            'children' => [
                [
                    'title' => 'My Membership',
                    'route' => 'user.membership',
                    'permission' => null,
                ],
                [
                    'title' => 'Membership Card',
                    'route' => 'user.membership.card',
                    'permission' => null,
                ],
                [
                    'title' => 'Payment History',
                    'route' => 'user.membership.payments',
                    'permission' => null,
                ],
            ],
        ],
        [
            'title' => 'Party Resources',
            'icon' => 'ri-file-list-3-line',
            'children' => [
                [
                    'title' => 'Documents',
                    'route' => 'user.resources.documents',
                    'permission' => 'view_documents',
                ],
                [
                    'title' => 'Events',
                    'route' => 'user.resources.events',
                    'permission' => 'view_events',
                ],
                [
                    'title' => 'News & Updates',
                    'route' => 'user.resources.news',
                    'permission' => 'view_news',
                ],
            ],
        ],
        [
            'title' => 'Volunteer Portal',
            'icon' => 'ri-team-line',
            'children' => [
                [
                    'title' => 'My Activities',
                    'route' => 'user.volunteer.activities',
                    'permission' => 'volunteer',
                ],
                [
                    'title' => 'Available Tasks',
                    'route' => 'user.volunteer.tasks',
                    'permission' => 'volunteer',
                ],
                [
                    'title' => 'Training Materials',
                    'route' => 'user.volunteer.training',
                    'permission' => 'volunteer',
                ],
            ],
        ],
        [
            'title' => 'Communication',
            'icon' => 'ri-message-2-line',
            'children' => [
                [
                    'title' => 'Messages',
                    'route' => 'user.messages',
                    'permission' => null,
                ],
                [
                    'title' => 'Notifications',
                    'route' => 'user.notifications',
                    'permission' => null,
                ],
                [
                    'title' => 'Feedback',
                    'route' => 'user.feedback',
                    'permission' => null,
                ],
            ],
        ],
        [
            'title' => 'Settings',
            'icon' => 'ri-settings-4-line',
            'children' => [
                [
                    'title' => 'Account Settings',
                    'route' => 'user.settings.account',
                    'permission' => null,
                ],
                [
                    'title' => 'Privacy Settings',
                    'route' => 'user.settings.privacy',
                    'permission' => null,
                ],
                [
                    'title' => 'Notification Preferences',
                    'route' => 'user.settings.notifications',
                    'permission' => null,
                ],
            ],
        ],
    ],
]; 