<?php

use craft\config\GeneralConfig;
use craft\helpers\App;

return GeneralConfig::create()
    ->allowAdminChanges(App::env('CRAFT_ALLOW_ADMIN_CHANGES') ?? true)
    ->devMode(App::env('CRAFT_DEV_MODE') ?? false)
    ->securityKey(App::env('CRAFT_SECURITY_KEY'))
    ->omitScriptNameInUrls()
    ->cpTrigger('admin')
    ->defaultWeekStartDay(0)
    ->useEmailAsUsername(true);
