<?php

return [
    [
        'value' => 'access portal',
        'label' => 'portal access',
        'group' => 'access',
    ],
    [
        'value' => 'access cashier',
        'label' => 'cashier access',
        'group' => 'access',
    ],
    [
        'value' => 'access customer app',
        'label' => 'customer app access',
        'group' => 'access',
        'requires' => [
            'services index',
            'products index',
            'properties index',
            'properties create',
            'properties update',
            'properties delete',
            'schedules index',
            'schedules read',
            'schedules update',
            'schedules cancel',
            'schedule change requests create',
            'feedbacks create',
            'notifications index',
            'notifications read',
            'credits index',
        ],
    ],
    [
        'value' => 'access employee app',
        'label' => 'employee app access',
        'group' => 'access',
        'requires' => [
            'teams index',
            'teams read',
            'feedbacks create',
            'notifications index',
            'notifications read',
            'deviations create',
        ],
    ],
    [
        'value' => 'customers index',
        'label' => 'index',
        'group' => 'customers',
    ],
    [
        'value' => 'customers create',
        'label' => 'create',
        'group' => 'customers',
    ],
    [
        'value' => 'customers read',
        'label' => 'read',
        'group' => 'customers',
    ],
    [
        'value' => 'customers update',
        'label' => 'update',
        'group' => 'customers',
    ],
    [
        'value' => 'customers delete',
        'label' => 'delete',
        'group' => 'customers',
    ],
    [
        'value' => 'customers restore',
        'label' => 'restore',
        'group' => 'customers',
    ],
    [
        'value' => 'customers wizard',
        'label' => 'wizard',
        'group' => 'customers',
    ],
    [
        'value' => 'customers primary address read',
        'label' => 'read primary address',
        'group' => 'customers',
    ],
    [
        'value' => 'customers primary address update',
        'label' => 'update primary address',
        'group' => 'customers',
    ],
    [
        'value' => 'customer invoice addresses index',
        'label' => 'invoice address index',
        'group' => 'customers',
    ],
    [
        'value' => 'customer invoice addresses create',
        'label' => 'create invoice address',
        'group' => 'customers',
    ],
    [
        'value' => 'customer invoice addresses update',
        'label' => 'update invoice address',
        'group' => 'customers',
    ],
    [
        'value' => 'customer schedules index',
        'label' => 'schedule index',
        'group' => 'customers',
    ],
    [
        'value' => 'customer schedule histories index',
        'label' => 'schedule history index',
        'group' => 'customers',
    ],
    [
        'value' => 'customer credits index',
        'label' => 'credit index',
        'group' => 'customers',
    ],
    [
        'value' => 'customer invoice addresses delete',
        'label' => 'delete invoice address',
        'group' => 'customers',
    ],
    [
        'value' => 'customer invoice addresses restore',
        'label' => 'restore invoice address',
        'group' => 'customers',
    ],
    [
        'value' => 'fixed prices index',
        'label' => 'index',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed prices create',
        'label' => 'create',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed prices read',
        'label' => 'read',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed prices update',
        'label' => 'update',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed prices delete',
        'label' => 'delete',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed prices restore',
        'label' => 'restore',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed price rows index',
        'label' => 'row index',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed price rows create',
        'label' => 'create row',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed price rows update',
        'label' => 'update row',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'fixed price rows delete',
        'label' => 'delete row',
        'group' => 'fixed prices',
    ],
    [
        'value' => 'customer discounts index',
        'label' => 'index',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'customer discounts create',
        'label' => 'create',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'customer discounts read',
        'label' => 'read',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'customer discounts update',
        'label' => 'update',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'customer discounts delete',
        'label' => 'delete',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'customer discounts restore',
        'label' => 'restore',
        'group' => 'customer discounts',
    ],
    [
        'value' => 'properties index',
        'label' => 'index',
        'group' => 'properties',
    ],
    [
        'value' => 'properties create',
        'label' => 'create',
        'group' => 'properties',
    ],
    [
        'value' => 'properties read',
        'label' => 'read',
        'group' => 'properties',
    ],
    [
        'value' => 'properties update',
        'label' => 'update',
        'group' => 'properties',
    ],
    [
        'value' => 'properties delete',
        'label' => 'delete',
        'group' => 'properties',
    ],
    [
        'value' => 'properties restore',
        'label' => 'restore',
        'group' => 'properties',
    ],
    [
        'value' => 'properties wizard',
        'label' => 'wizard',
        'group' => 'properties',
    ],
    [
        'value' => 'subscriptions index',
        'label' => 'index',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions create',
        'label' => 'create',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions read',
        'label' => 'read',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions update',
        'label' => 'update',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions pause',
        'label' => 'pause',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions continue',
        'label' => 'continue',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions delete',
        'label' => 'delete',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions restore',
        'label' => 'restore',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscriptions wizard',
        'label' => 'wizard',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscription tasks index',
        'label' => 'task index',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscription schedules index',
        'label' => 'schedule index',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscription tasks create',
        'label' => 'create task',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscription tasks update',
        'label' => 'update task',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'subscription tasks delete',
        'label' => 'delete task',
        'group' => 'subscriptions',
    ],
    [
        'value' => 'companies index',
        'label' => 'index',
        'group' => 'companies',
    ],
    [
        'value' => 'companies create',
        'label' => 'create',
        'group' => 'companies',
    ],
    [
        'value' => 'companies read',
        'label' => 'read',
        'group' => 'companies',
    ],
    [
        'value' => 'companies update',
        'label' => 'update',
        'group' => 'companies',
    ],
    [
        'value' => 'companies delete',
        'label' => 'delete',
        'group' => 'companies',
    ],
    [
        'value' => 'companies restore',
        'label' => 'restore',
        'group' => 'companies',
    ],
    [
        'value' => 'companies wizard',
        'label' => 'wizard',
        'group' => 'companies',
    ],
    [
        'value' => 'companies primary address read',
        'label' => 'read primary address',
        'group' => 'companies',
    ],
    [
        'value' => 'companies primary address update',
        'label' => 'update primary address',
        'group' => 'companies',
    ],
    [
        'value' => 'company invoice addresses index',
        'label' => 'invoice address index',
        'group' => 'companies',
    ],
    [
        'value' => 'company invoice addresses create',
        'label' => 'create invoice address',
        'group' => 'companies',
    ],
    [
        'value' => 'company invoice addresses update',
        'label' => 'update invoice address',
        'group' => 'companies',
    ],
    [
        'value' => 'company schedules index',
        'label' => 'schedule index',
        'group' => 'companies',
    ],
    [
        'value' => 'company schedule histories index',
        'label' => 'schedule history index',
        'group' => 'companies',
    ],
    [
        'value' => 'company credits index',
        'label' => 'credit index',
        'group' => 'companies',
    ],
    [
        'value' => 'company invoice addresses delete',
        'label' => 'delete invoice address',
        'group' => 'companies',
    ],
    [
        'value' => 'company invoice addresses restore',
        'label' => 'restore invoice address',
        'group' => 'companies',
    ],
    [
        'value' => 'company properties index',
        'label' => 'index',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties create',
        'label' => 'create',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties read',
        'label' => 'read',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties update',
        'label' => 'update',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties delete',
        'label' => 'delete',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties restore',
        'label' => 'restore',
        'group' => 'company properties',
    ],
    [
        'value' => 'company properties wizard',
        'label' => 'wizard',
        'group' => 'company properties',
    ],
    [
        'value' => 'company subscriptions index',
        'label' => 'index',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions create',
        'label' => 'create',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions read',
        'label' => 'read',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions update',
        'label' => 'update',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions pause',
        'label' => 'pause',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions continue',
        'label' => 'continue',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions delete',
        'label' => 'delete',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions restore',
        'label' => 'restore',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscriptions wizard',
        'label' => 'wizard',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscription tasks index',
        'label' => 'task index',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscription tasks create',
        'label' => 'create task',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscription tasks update',
        'label' => 'update task',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company subscription tasks delete',
        'label' => 'delete task',
        'group' => 'company subscriptions',
    ],
    [
        'value' => 'company fixed prices index',
        'label' => 'index',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed prices create',
        'label' => 'create',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed prices read',
        'label' => 'read',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed prices update',
        'label' => 'update',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed prices delete',
        'label' => 'delete',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed prices restore',
        'label' => 'restore',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed price rows index',
        'label' => 'row index',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed price rows create',
        'label' => 'create row',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed price rows update',
        'label' => 'update row',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company fixed price rows delete',
        'label' => 'delete row',
        'group' => 'company fixed prices',
    ],
    [
        'value' => 'company discounts index',
        'label' => 'index',
        'group' => 'company discounts',
    ],
    [
        'value' => 'company discounts create',
        'label' => 'create',
        'group' => 'company discounts',
    ],
    [
        'value' => 'company discounts read',
        'label' => 'read',
        'group' => 'company discounts',
    ],
    [
        'value' => 'company discounts update',
        'label' => 'update',
        'group' => 'company discounts',
    ],
    [
        'value' => 'company discounts delete',
        'label' => 'delete',
        'group' => 'company discounts',
    ],
    [
        'value' => 'company discounts restore',
        'label' => 'restore',
        'group' => 'company discounts',
    ],
    [
        'value' => 'schedules index',
        'label' => 'index',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedules read',
        'label' => 'read',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedules update',
        'label' => 'update',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedules cancel',
        'label' => 'cancel',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedules reschedule',
        'label' => 'reschedule',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers index',
        'label' => 'worker index',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers read',
        'label' => 'read worker',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers create',
        'label' => 'create worker',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers enable',
        'label' => 'enable worker',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers disable',
        'label' => 'disable worker',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule workers update attendance',
        'label' => 'update attendance',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule tasks index',
        'label' => 'task index',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule tasks create',
        'label' => 'create task',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule tasks update',
        'label' => 'update task',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule tasks delete',
        'label' => 'delete task',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule change requests index',
        'label' => 'change request index',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule change requests create',
        'label' => 'create change request',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule change requests approve',
        'label' => 'approve change request',
        'group' => 'schedules',
    ],
    [
        'value' => 'schedule change requests reject',
        'label' => 'reject change request',
        'group' => 'schedules',
    ],
    [
        'value' => 'orders index',
        'label' => 'index',
        'group' => 'orders',
    ],
    [
        'value' => 'orders read',
        'label' => 'read',
        'group' => 'orders',
    ],
    [
        'value' => 'order rows index',
        'label' => 'row index',
        'group' => 'orders',
    ],
    [
        'value' => 'order rows create',
        'label' => 'create row',
        'group' => 'orders',
    ],
    [
        'value' => 'order rows update',
        'label' => 'update row',
        'group' => 'orders',
    ],
    [
        'value' => 'order rows delete',
        'label' => 'delete row',
        'group' => 'orders',
    ],
    [
        'value' => 'invoices index',
        'label' => 'index',
        'group' => 'invoices',
    ],
    [
        'value' => 'invoices read',
        'label' => 'read',
        'group' => 'invoices',
    ],
    [
        'value' => 'invoices create fortnox',
        'label' => 'create fortnox',
        'group' => 'invoices',
    ],
    [
        'value' => 'invoices send',
        'label' => 'send',
        'group' => 'invoices',
    ],
    [
        'value' => 'invoices update',
        'label' => 'update',
        'group' => 'invoices',
    ],
    [
        'value' => 'invoices cancel',
        'label' => 'cancel',
        'group' => 'invoices',
    ],
    [
        'value' => 'employees index',
        'label' => 'index',
        'group' => 'employees',
    ],
    [
        'value' => 'employees create',
        'label' => 'create',
        'group' => 'employees',
    ],
    [
        'value' => 'employees read',
        'label' => 'read',
        'group' => 'employees',
    ],
    [
        'value' => 'employees update',
        'label' => 'update',
        'group' => 'employees',
    ],
    [
        'value' => 'employees delete',
        'label' => 'delete',
        'group' => 'employees',
    ],
    [
        'value' => 'employees restore',
        'label' => 'restore',
        'group' => 'employees',
    ],
    [
        'value' => 'employee roles update',
        'label' => 'update role',
        'group' => 'employees',
    ],
    [
        'value' => 'employee roles appoint superadmin',
        'label' => 'appoint superadmin',
        'group' => 'employees',
    ],
    [
        'value' => 'employees wizard',
        'label' => 'wizard',
        'group' => 'employees',
    ],
    [
        'value' => 'teams index',
        'label' => 'index',
        'group' => 'teams',
    ],
    [
        'value' => 'teams create',
        'label' => 'create',
        'group' => 'teams',
    ],
    [
        'value' => 'teams read',
        'label' => 'read',
        'group' => 'teams',
    ],
    [
        'value' => 'teams update',
        'label' => 'update',
        'group' => 'teams',
    ],
    [
        'value' => 'teams delete',
        'label' => 'delete',
        'group' => 'teams',
    ],
    [
        'value' => 'teams restore',
        'label' => 'restore',
        'group' => 'teams',
    ],
    [
        'value' => 'deviations index',
        'label' => 'index',
        'group' => 'deviations',
    ],
    [
        'value' => 'deviations create',
        'label' => 'create',
        'group' => 'deviations',
    ],
    [
        'value' => 'deviations read',
        'label' => 'read',
        'group' => 'deviations',
    ],
    [
        'value' => 'deviations handle',
        'label' => 'handle',
        'group' => 'deviations',
    ],
    [
        'value' => 'time reports index',
        'label' => 'index',
        'group' => 'time reports',
    ],
    [
        'value' => 'services index',
        'label' => 'index',
        'group' => 'services',
    ],
    [
        'value' => 'services create',
        'label' => 'create',
        'group' => 'services',
    ],
    [
        'value' => 'services read',
        'label' => 'read',
        'group' => 'services',
    ],
    [
        'value' => 'services update',
        'label' => 'update',
        'group' => 'services',
    ],
    [
        'value' => 'services delete',
        'label' => 'delete',
        'group' => 'services',
    ],
    [
        'value' => 'services restore',
        'label' => 'restore',
        'group' => 'services',
    ],
    [
        'value' => 'service tasks index',
        'label' => 'task index',
        'group' => 'services',
    ],
    [
        'value' => 'service tasks create',
        'label' => 'create task',
        'group' => 'services',
    ],
    [
        'value' => 'service tasks update',
        'label' => 'update task',
        'group' => 'services',
    ],
    [
        'value' => 'service tasks delete',
        'label' => 'delete task',
        'group' => 'services',
    ],
    [
        'value' => 'service translations update',
        'label' => 'update translations',
        'group' => 'services',
    ],

    [
        'value' => 'service quarters index',
        'label' => 'index',
        'group' => 'service quarters',
    ],
    [
        'value' => 'service quarters create',
        'label' => 'create',
        'group' => 'service quarters',
    ],
    [
        'value' => 'service quarters read',
        'label' => 'read',
        'group' => 'service quarters',
    ],
    [
        'value' => 'service quarters update',
        'label' => 'update',
        'group' => 'service quarters',
    ],
    [
        'value' => 'service quarters delete',
        'label' => 'delete',
        'group' => 'service quarters',
    ],
    [
        'value' => 'addons index',
        'label' => 'index',
        'group' => 'addons',
    ],
    [
        'value' => 'addons create',
        'label' => 'create',
        'group' => 'addons',
    ],
    [
        'value' => 'addons read',
        'label' => 'read',
        'group' => 'addons',
    ],
    [
        'value' => 'addons update',
        'label' => 'update',
        'group' => 'addons',
    ],
    [
        'value' => 'addons delete',
        'label' => 'delete',
        'group' => 'addons',
    ],
    [
        'value' => 'addons restore',
        'label' => 'restore',
        'group' => 'addons',
    ],
    [
        'value' => 'addon tasks index',
        'label' => 'task index',
        'group' => 'addons',
    ],
    [
        'value' => 'addon tasks create',
        'label' => 'create task',
        'group' => 'addons',
    ],
    [
        'value' => 'addon tasks update',
        'label' => 'update task',
        'group' => 'addons',
    ],
    [
        'value' => 'addon tasks delete',
        'label' => 'delete task',
        'group' => 'addons',
    ],
    [
        'value' => 'addon translations update',
        'label' => 'update translations',
        'group' => 'addons',
    ],
    [
        'value' => 'products index',
        'label' => 'index',
        'group' => 'products',
    ],
    [
        'value' => 'products create',
        'label' => 'create',
        'group' => 'products',
    ],
    [
        'value' => 'products read',
        'label' => 'read',
        'group' => 'products',
    ],
    [
        'value' => 'products update',
        'label' => 'update',
        'group' => 'products',
    ],
    [
        'value' => 'products delete',
        'label' => 'delete',
        'group' => 'products',
    ],
    [
        'value' => 'products restore',
        'label' => 'restore',
        'group' => 'products',
    ],
    [
        'value' => 'product translations update',
        'label' => 'update translations',
        'group' => 'products',
    ],
    [
        'value' => 'price adjustment index',
        'label' => 'index',
        'group' => 'price adjustments',
    ],
    [
        'value' => 'price adjustment create',
        'label' => 'create',
        'group' => 'price adjustments',
    ],
    [
        'value' => 'price adjustment update',
        'label' => 'update',
        'group' => 'price adjustments',
    ],
    [
        'value' => 'price adjustment delete',
        'label' => 'delete',
        'group' => 'price adjustments',
    ],
    [
        'value' => 'price adjustment read',
        'label' => 'read',
        'group' => 'price adjustments',
    ],
    [
        'value' => 'blockdays index',
        'label' => 'index',
        'group' => 'blockdays',
    ],
    [
        'value' => 'blockdays create',
        'label' => 'create',
        'group' => 'blockdays',
    ],
    [
        'value' => 'blockdays update',
        'label' => 'update',
        'group' => 'blockdays',
    ],
    [
        'value' => 'blockdays delete',
        'label' => 'delete',
        'group' => 'blockdays',
    ],
    [
        'value' => 'roles index',
        'label' => 'index',
        'group' => 'roles',
    ],
    [
        'value' => 'roles create',
        'label' => 'create',
        'group' => 'roles',
    ],
    [
        'value' => 'roles update',
        'label' => 'update',
        'group' => 'roles',
    ],
    [
        'value' => 'roles delete',
        'label' => 'delete',
        'group' => 'roles',
    ],
    [
        'value' => 'system settings index',
        'label' => 'index',
        'group' => 'system settings',
    ],
    [
        'value' => 'system settings update',
        'label' => 'update',
        'group' => 'system settings',
    ],
    [
        'value' => 'key places index',
        'label' => 'index',
        'group' => 'key places',
    ],
    [
        'value' => 'feedbacks index',
        'label' => 'index',
        'group' => 'feedbacks',
    ],
    [
        'value' => 'feedbacks create',
        'label' => 'create',
        'group' => 'feedbacks',
    ],
    [
        'value' => 'feedbacks delete',
        'label' => 'delete',
        'group' => 'feedbacks',
    ],
    [
        'value' => 'feedbacks restore',
        'label' => 'restore',
        'group' => 'feedbacks',
    ],
    [
        'value' => 'activity logs index',
        'label' => 'index',
        'group' => 'activity logs',
    ],
    [
        'value' => 'activity logs delete',
        'label' => 'delete',
        'group' => 'activity logs',
    ],
    [
        'value' => 'authentication logs index',
        'label' => 'index',
        'group' => 'authentication logs',
    ],
    [
        'value' => 'authentication logs delete',
        'label' => 'delete',
        'group' => 'authentication logs',
    ],
    [
        'value' => 'credits index',
        'label' => 'index',
        'group' => 'credits',
    ],
    [
        'value' => 'notifications index',
        'label' => 'index',
        'group' => 'notifications',
    ],
    [
        'value' => 'notifications read',
        'label' => 'read',
        'group' => 'notifications',
    ],
    [
        'value' => 'notifications send',
        'label' => 'send',
        'group' => 'notifications',
    ],
    [
        'value' => 'credits create',
        'label' => 'create',
        'group' => 'credits',
    ],
    [
        'value' => 'credits update',
        'label' => 'update',
        'group' => 'credits',
    ],
    [
        'value' => 'credits delete',
        'label' => 'delete',
        'group' => 'credits',
    ],
    [
        'value' => 'schedules history create',
        'label' => 'create history',
        'group' => 'schedules',
    ],
    [
        'value' => 'customer rut co applicant index',
        'label' => 'rut co applicant index',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant create',
        'label' => 'create rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant enable',
        'label' => 'enable rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant disable',
        'label' => 'disable rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant pause',
        'label' => 'pause rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant continue',
        'label' => 'continue rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant update',
        'label' => 'update rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'customer rut co applicant delete',
        'label' => 'delete rut co applicant',
        'group' => 'customers',
    ],
    [
        'value' => 'unassign subscriptions index',
        'label' => 'index',
        'group' => 'unassign subscriptions',
    ],
    [
        'value' => 'unassign subscriptions create',
        'label' => 'create',
        'group' => 'unassign subscriptions',
    ],
    [
        'value' => 'unassign subscriptions update',
        'label' => 'update',
        'group' => 'unassign subscriptions',
    ],
    [
        'value' => 'unassign subscriptions delete',
        'label' => 'delete',
        'group' => 'unassign subscriptions',
    ],
    [
        'value' => 'leave registrations index',
        'label' => 'index',
        'group' => 'leave registrations',
    ],
    [
        'value' => 'leave registrations create',
        'label' => 'create',
        'group' => 'leave registrations',
    ],
    [
        'value' => 'leave registrations update',
        'label' => 'update',
        'group' => 'leave registrations',
    ],
    [
        'value' => 'leave registrations delete',
        'label' => 'delete',
        'group' => 'leave registrations',
    ],
    [
        'value' => 'leave registrations done',
        'label' => 'done',
        'group' => 'leave registrations',
    ],
    [
        'value' => 'time adjustments index',
        'label' => 'index',
        'group' => 'time adjustments',
    ],
    [
        'value' => 'time adjustments create',
        'label' => 'create',
        'group' => 'time adjustments',
    ],
    [
        'value' => 'time adjustments update',
        'label' => 'update',
        'group' => 'time adjustments',
    ],
    [
        'value' => 'time adjustments delete',
        'label' => 'delete',
        'group' => 'time adjustments',
    ],
    [
        'value' => 'stores index',
        'label' => 'index',
        'group' => 'stores',
    ],
    [
        'value' => 'stores read',
        'label' => 'read',
        'group' => 'stores',
    ],
    [
        'value' => 'stores create',
        'label' => 'create',
        'group' => 'stores',
    ],
    [
        'value' => 'stores update',
        'label' => 'update',
        'group' => 'stores',
    ],
    [
        'value' => 'stores delete',
        'label' => 'delete',
        'group' => 'stores',
    ],
    [
        'value' => 'stores restore',
        'label' => 'restore',
        'group' => 'stores',
    ],
    [
        'value' => 'categories index',
        'label' => 'index',
        'group' => 'categories',
    ],
    [
        'value' => 'categories create',
        'label' => 'create',
        'group' => 'categories',
    ],
    [
        'value' => 'categories update',
        'label' => 'update',
        'group' => 'categories',
    ],
    [
        'value' => 'categories delete',
        'label' => 'delete',
        'group' => 'categories',
    ],
    [
        'value' => 'categories restore',
        'label' => 'restore',
        'group' => 'categories',
    ],
    [
        'value' => 'categories translations update',
        'label' => 'update translations',
        'group' => 'categories',
    ],
    [
        'value' => 'laundry orders index',
        'label' => 'index',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry orders create',
        'label' => 'create',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry orders update',
        'label' => 'update',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry orders delete',
        'label' => 'delete',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry orders restore',
        'label' => 'restore',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry orders read',
        'label' => 'read',
        'group' => 'laundry orders',
    ],
    [
        'value' => 'laundry order products create',
        'label' => 'create',
        'group' => 'laundry order products',
    ],
    [
        'value' => 'laundry order products update',
        'label' => 'update',
        'group' => 'laundry order products',
    ],
    [
        'value' => 'laundry order products delete',
        'label' => 'delete',
        'group' => 'laundry order products',
    ],
    [
        'value' => 'laundry order products read',
        'label' => 'read',
        'group' => 'laundry order products',
    ],
    [
        'value' => 'laundry order schedules create',
        'label' => 'create',
        'group' => 'laundry order schedules',
    ],
    [
        'value' => 'laundry order schedules update',
        'label' => 'update',
        'group' => 'laundry order schedules',
    ],
    [
        'value' => 'laundry order schedules delete',
        'label' => 'delete',
        'group' => 'laundry order schedules',
    ],
    [
        'value' => 'laundry order schedules read',
        'label' => 'read',
        'group' => 'laundry order schedules',
    ],
    [
        'value' => 'laundry order histories read',
        'label' => 'read',
        'group' => 'laundry order histories',
    ],
];
