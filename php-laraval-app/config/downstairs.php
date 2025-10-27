<?php

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\SettingTypeEnum;

return [
    'apiVersions' => [
        'v0' => '0.21.0',
        'v1' => '1.30.2',
        'v2' => '2.0.0',
    ],
    'cache' => [
        'ttl' => 60 * 60 * 24,
    ],
    'subscription' => [
        'refillSequence' => env('SUBSCRIPTION_REFILL_SEQUENCE', 52),
        'collisionWeeksSimulation' => env('SUBSCRIPTION_COLLISION_WEEKS_SIMULATION', 52),
    ],
    'schedule' => [
        'employee' => [
            'minStartMinutes' => env('START_JOB_MIN_MINUTE', 10),
            'maxStartMinutes' => env('START_JOB_MAX_MINUTE', 10),
            'maxEndMinutes' => env('END_JOB_MAX_MINUTE', 10),
            'minEndMinutes' => env('END_JOB_MIN_MINUTE', 15),
        ],
        'laundry' => [
            'quarters' => env('LAUNDRY_QUARTERS', 1),
        ],
        'run' => [
            'subscription' => env('SUBSCRIPTION_RUN_TIME', '0 0 * * *'),
            'health' => env('HEALTH_CHECK_RUN_TIME', '* * * * *'),
            'logs' => env('BACKUP_LOGS_RUN_TIME', '0 * * * *'),
            'sendLeaveRegistration' => env('SEND_LEAVE_REGISTRATION_RUN_TIME', '0 0 1 * *'),
            'priceAdjustment' => env('RUN_PRICE_ADJUSTMENT_TIME', '0 0 * * *'),
        ],
        'flag' => [
            'healthCheck' => env('SCHEDULER_HEALTH_CHECK_FLAG', true),
            'generateSchedules' => env('SCHEDULER_GENERATE_SCHEDULES_FLAG', true),
            'notifyReminder' => env('SCHEDULER_NOTIFY_REMINDER_FLAG', true),
            'upcomingReminder' => env('SCHEDULER_UPCOMING_REMINDER_FLAG', true),
            'notStartedCheck' => env('SCHEDULER_NOT_STARTED_CHECK_FLAG', true),
            'renewFortnoxToken' => env('SCHEDULER_RENEW_FORTNOX_TOKEN_FLAG', true),
            'importInvoice' => env('SCHEDULER_IMPORT_INVOICE_FLAG', true),
            'createFortnoxInvoice' => env('SCHEDULER_CREATE_FORTNOX_INVOICE_FLAG', true),
            'sentFortnoxInvoice' => env('SCHEDULER_SENT_FORTNOX_INVOICE_FLAG', true),
            'syncFortnox' => env('SCHEDULER_SYNC_FORTNOX_FLAG', true),
            'sendLeaveRegistration' => env('SCHEDULER_SEND_LEAVE_REGISTRATION_FLAG', true),
            'removeEndedSubscription' => env('SCHEDULER_REMOVE_ENDED_SUBSCRIPTION_FLAG', true),
            'removeEndedDiscount' => env('SCHEDULER_REMOVE_ENDED_DISCOUNT_FLAG', true),
            'backupLogs' => env('SCHEDULER_BACKUP_LOGS_FLAG', true),
            'pollingFortnoxInvoiceStatus' => env('SCHEDULER_POLLING_FORTNOX_INVOICE_STATUS_FLAG', true),
            'cleanActivityLog' => env('SCHEDULER_CLEAN_ACTIVITY_LOG_FLAG', true),
            'runPriceAdjustment' => env('SCHEDULER_RUN_PRICE_ADJUSTMENT_FLAG', true),

        ],
    ],
    'test' => [
        'cellphone' => env('TEST_CELLPHONE'),
        'otp' => env('TEST_OTP', 7284),
        'city_id' => 55871,
    ],
    'pageSize' => 50,
    'products' => [
        'systemIds' => [5, 6, 8],
        'transport' => [
            'id' => 5,
        ],
        'material' => [
            'id' => 6,
        ],
        'productSalesMisc' => [
            'id' => 8,
        ],
    ],
    'categories' => [
        'systemIds' => [1, 2, 3, 4],
        'cleaning' => [
            'id' => 1,
        ],
        'miscellaneous' => [
            'id' => 2,
        ],
        'laundry' => [
            'id' => 3,
        ],
        'store' => [
            'id' => 4,
        ],
    ],
    'services' => [
        'laundry' => [
            'private' => [
                'id' => 2,
            ],
            'company' => [
                'id' => 4,
            ],
        ],
    ],
    'addons' => [
        'laundry' => [
            'id' => 1,
        ],
    ],
    'laundry' => [
        'mainStore' => [
            'id' => env('DEFAULT_LAUNDRY_MAIN_STORE_ID', 1),
        ],
        'preference' => [
            'normal' => [
                'id' => 1,
            ],
        ],
    ],
    'globalSettings' => [
        [
            'key' => GlobalSettingEnum::MaxMonthShow(),
            'value' => '6',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Max month to show in customer calendar',
                'nn_NO' => 'Max måned for å vise i kundekalenderen',
                'sv_SE' => 'Max månad att visa i kundkalendern',
            ],
        ],
        [
            'key' => GlobalSettingEnum::MaxBannerShow(),
            'value' => '3',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Max banner to show in customer home screen',
                'nn_NO' => 'Max banner for å vise i kundens hjemmeskjerm',
                'sv_SE' => 'Max banner att visa i kundens hemskärm',
            ],
        ],
        [
            'key' => GlobalSettingEnum::RequestTimeoutInterval(),
            'value' => '500',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Time for waiting to request again',
                'nn_NO' => 'Tid for å vente på å be om igjen',
                'sv_SE' => 'Tid för att vänta på att begära igen',
            ],
        ],
        [
            'key' => GlobalSettingEnum::ResendOtpCounter(),
            'value' => '60',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Time for waiting to resend OTP',
                'nn_NO' => 'Tid for å vente på å sende OTP på nytt',
                'sv_SE' => 'Tid för att vänta på att skicka OTP igen',
            ],
        ],
        [
            'key' => GlobalSettingEnum::CreditRefundTimeWindow(),
            'value' => '72',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Customer can receive refund credit within this hours after cancel',
                'nn_NO' => 'Kunden kan motta refusjon kreditt innen denne timen etter avbestilling',
                'sv_SE' => 'Kunden kan få återbetalning inom dessa timmar efter avbokning',
            ],
        ],
        [
            'key' => GlobalSettingEnum::CreditMinutePerCredit(),
            'value' => '15',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Every this minutes equal 1 credit',
                'nn_NO' => 'Hver denne minutter tilsvarer 1 kreditt',
                'sv_SE' => 'Varje denna minut motsvarar 1 kredit',
            ],
        ],
        [
            'key' => GlobalSettingEnum::CreditExpirationDays(),
            'value' => '365',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Total amount of days the credit will expire',
                'nn_NO' => 'Totalt antall dager kreditten vil utløpe',
                'sv_SE' => 'Totalt antal dagar krediten kommer att löpa ut',
            ],
        ],
        [
            'key' => GlobalSettingEnum::StartJobMaxDistance(),
            'value' => '100',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => "Max distance between worker & customer's property when they starting a job (in meters)",
                'nn_NO' => 'Maksimal avstand mellom arbeider & kundens eiendom når de starter en jobb (i meter)',
                'sv_SE' => 'Maximalt avstånd mellan arbetaren & kundens egendom när de startar ett jobb (i meter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::OtpLength(),
            'value' => '4',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Length of OTP',
                'nn_NO' => 'Lengde på OTP',
                'sv_SE' => 'Längd på OTP',
            ],
        ],
        [
            'key' => GlobalSettingEnum::StartJobLateTime(),
            'value' => '15',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'How long is considered as late for worker to start their job (in minutes)',
                'nn_NO' => 'Hvor lenge regnes som sent for arbeideren å starte jobben sin (i minutter)',
                'sv_SE' => 'Hur länge anses det vara sent för arbetaren att starta sitt jobb (i minuter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::EndJobLateTime(),
            'value' => '15',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'How long is considered as late for worker to end their job (in minutes)',
                'nn_NO' => 'Hvor lenge regnes som sent for arbeideren å avslutte jobben sin (i minutter)',
                'sv_SE' => 'Hur länge anses det vara sent för arbetaren att avsluta sitt jobb (i minuter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::ScheduleStartReminderMinutes(),
            'value' => '60',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'How long before schedule start time to send reminder to worker (in minutes)',
                'nn_NO' => 'Hvor lenge før planlagt starttid for å sende påminnelse til arbeideren (i minutter)',
                'sv_SE' => 'Hur långt innan schemalagd starttid för att skicka påminnelse till arbetaren '.
                        '(i minuter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::ScheduleEndReminderMinutes(),
            'value' => '600',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'How long the worker get a reminder to end the job (in minutes)',
                'nn_NO' => 'Hvor lenge arbeideren får en påminnelse om å avslutte jobben (i minutter)',
                'sv_SE' => 'Hur länge arbetaren får en påminnelse om att avsluta jobbet (i minuter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::DefaultEmailSubject(),
            'value' => 'Cancel Subscription',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'Default Cancel Subscription Subject',
                'nn_NO' => 'Standard avbryt abonnementsemne',
                'sv_SE' => 'Standard avbryt prenumeration ämne',
            ],
        ],
        [
            'key' => GlobalSettingEnum::InvoiceSentDate(),
            'value' => '5',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'The date in the month when the invoice is sent',
                'nn_NO' => 'Datoen i måneden når fakturaen sendes',
                'sv_SE' => 'Datumet i månaden när fakturan skickas',
            ],
        ],
        [
            'key' => GlobalSettingEnum::InvoiceDueDays(),
            'value' => '30',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Number of days for the distance between sent date to due date',
                'nn_NO' => 'Avstanden i dager fra sendt dato til forfallsdato for fakturaen',
                'sv_SE' => 'Antal dagar för avståndet mellan skickat datum till förfallodatum',
            ],
        ],
        [
            'key' => GlobalSettingEnum::SubscriptionRefillSequence(),
            'value' => '52',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Generate constant active booking by system based on subscription',
                'nn_NO' => 'Generer konstant aktiv bestilling av system basert på abonnement',
                'sv_SE' => 'Generera konstant aktiv bokning av system baserat på prenumeration',
            ],
        ],
        [
            'key' => GlobalSettingEnum::UrlDownstairsSupport(),
            'value' => 'https://www.downstairs.se/kontakt/',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'URL that redirect to support page from customer application',
                'nn_NO' => 'URL som omdirigerer til støtteside fra kundeapplikasjon',
                'sv_SE' => 'URL som omdirigeras till supportsidan från kundapplikationen',
            ],
        ],
        [
            'key' => GlobalSettingEnum::UrlDownstairsPrivacyPolicy(),
            'value' => 'https://www.downstairs.se/villkor/',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'URL that redirect to privacy policy page from customer application',
                'nn_NO' => 'URL som omdirigerer til siden for personvernerklæring fra kundeapplikasjonen',
                'sv_SE' => 'URL som omdirigerar till sekretesspolicysidan från kundapplikationen',
            ],
        ],
        [
            'key' => GlobalSettingEnum::UrlDownstairsTermsOfService(),
            'value' => 'https://www.downstairs.se/villkor/',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'URL that redirect to terms of service page from customer application',
                'nn_NO' => 'URL som omdirigerer til siden med vilkår for bruk fra kundeapplikasjonen',
                'sv_SE' => 'URL som omdirigerar till användarvillkorsidan från kundapplikationen',
            ],
        ],
        [
            'key' => GlobalSettingEnum::UrlDownstairsLegal(),
            'value' => 'https://www.downstairs.se/om-oss/',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'URL that redirect to legal page from customer application',
                'nn_NO' => 'URL som omdirigerer til juridisk side fra kundeapplikasjon',
                'sv_SE' => 'URL som omdirigerar till juridisk sida från kundapplikation',
            ],
        ],
        [
            'key' => GlobalSettingEnum::EmailCancelSubscription(),
            'value' => 'order@downstairs.se',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'Email address to make the cancel subscription from customer application',
                'nn_NO' => 'E-postadresse for å kansellere abonnementet fra kundeapplikasjonen',
                'sv_SE' => 'E-postadress för att avsluta prenumerationen från kundansökan',
            ],
        ],
        [
            'key' => GlobalSettingEnum::EndJobEarlyTime(),
            'value' => '15',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'Minimum time considered too early'.
                    ' from the end time for a worker to end work (in minutes)',
                'nn_NO' => 'Minimum tid som regnes som for tidlig'.
                    ' fra slutttid for en arbeider å avslutte arbeidet (i minutter)',
                'sv_SE' => 'Minsta tid som anses för tidigt'.
                    'från sluttiden för en arbetare att avsluta arbetet (i minuter)',
            ],
        ],
        [
            'key' => GlobalSettingEnum::DefaultShownTeam(),
            'value' => '1,2,3',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'Default team to show in schedule page initial load',
                'nn_NO' => 'Standard team for å vise i planleggingssiden første belastning',
                'sv_SE' => 'Standardlag att visa i schemaläggningssidan initial belastning',
            ],
        ],
        [
            'key' => GlobalSettingEnum::DefaultMinHourShow(),
            'value' => '07:00',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'Default mininum hour to show in schedule page',
                'nn_NO' => 'Standard mininum time for å vise i planleggingssiden',
                'sv_SE' => 'Standard mininum tid att visa i schemaläggningssidan',
            ],
        ],
        [
            'key' => GlobalSettingEnum::DefaultMaxHourShow(),
            'value' => '18:00',
            'type' => SettingTypeEnum::String(),
            'description' => [
                'en_US' => 'Default maximum hour to show in schedule page',
                'nn_NO' => 'Standard maksimal tid for å vise i planleggingssiden',
                'sv_SE' => 'Standard maximal tid att visa i schemaläggningssidan',
            ],
        ],
        [
            'key' => GlobalSettingEnum::AbsenceRescheduling(),
            'value' => '7',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'The number of days ahead that need rescheduling worker attention',
                'nn_NO' => 'Antalet dagar framåt som kräver omläggning av arbetstagarens uppmärksamhet',
                'sv_SE' => 'Antall dager fremover som trenger omplanlegging av arbeiderens oppmerksomhet',
            ],
        ],
        [
            'key' => GlobalSettingEnum::MaxProductAddTime(),
            'value' => '12',
            'type' => SettingTypeEnum::Integer(),
            'description' => [
                'en_US' => 'The maximum hours before booking when the addon can be added',
                'nn_NO' => 'Maksimalt antall timer før bestilling når tillegget kan legges til',
                'sv_SE' => 'Maximala timmar innan bokning när tillägget kan läggas till',
            ],
        ],
    ],
];
