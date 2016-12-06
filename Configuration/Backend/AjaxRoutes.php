<?php

/**
 * Definitions for routes provided by EXT:be_acl
 */
return [
    // Dispatch the permissions actions
    'user_access_permissions' => [
        'path' => '/users/access/permissions',
        'target' => \JBartels\BeAcl\Controller\PermissionAjaxController::class . '::dispatch'
    ]
];
