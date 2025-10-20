# Custom Placeholders Guide

Pachetul oferă mai multe modalități de a defini placeholders custom pentru traduceri.

## 1. Prin Fișierul de Configurare (Recomandat pentru valori statice)

Publică fișierul de configurare:

```bash
php artisan vendor:publish --tag=multilanguage-config
```

Apoi editează `config/multilanguage.php`:

```php
return [
    'placeholders' => [
        'company_name' => 'My Company Ltd',
        'support_email' => 'support@mycompany.com',
        'support_phone' => '+40 123 456 789',
        'copyright_year' => date('Y'),
        'vat_number' => 'RO12345678',
    ],
];
```

**Utilizare în traduceri:**

```php
// În fișierele de traducere
"footer.copyright" => "© :copyright_year :company_name. All rights reserved.",
"contact.info" => "Contact us at :support_email or call :support_phone",
```

## 2. Prin Cod Static (Recomandat pentru valori dinamice)

În `AppServiceProvider` sau alt service provider:

```php
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;

public function boot()
{
    // Setează mai multe placeholders o dată
    LocaleDataPlaceholders::setCustomPlaceholders([
        'user_count' => User::count(),
        'latest_post' => Post::latest()->first()?->title,
        'site_status' => Cache::get('site_status', 'online'),
    ]);

    // Sau adaugă individual
    LocaleDataPlaceholders::addCustomPlaceholder('active_users', User::where('active', true)->count());
}
```

## 3. Runtime Placeholders (În Controller/View)

Pentru valori specifice contextului curent:

```php
// În controller
public function show(User $user)
{
    return view('profile', [
        'user' => $user,
        'greeting' => localedata()->get('greetings.welcome', null, [
            'username' => $user->name,
            'last_login' => $user->last_login_at->diffForHumans(),
        ])
    ]);
}
```

## Placeholders Default Disponibile

Pachetul vine cu placeholders default pentru date și timp:

### Date:

-   `:year` - 2025
-   `:month` - 10
-   `:month_name` - October
-   `:month_short` - Oct
-   `:day` - 20
-   `:day_name` - Monday
-   `:day_short` - Mon
-   `:quarter` - 4

### Timp:

-   `:hour` - 14 (24h)
-   `:hour_12` - 2 (12h)
-   `:minute` - 30
-   `:second` - 45
-   `:am_pm` - AM/PM
-   `:am_pm_lower` - am/pm

### Formate Complete:

-   `:date` - 2025-10-20
-   `:date_formatted` - 20/10/2025
-   `:date_us` - 10/20/2025
-   `:time` - 14:30:45
-   `:time_short` - 14:30
-   `:datetime` - 2025-10-20 14:30:45
-   `:timestamp` - Unix timestamp
-   `:iso` - 2025-10-20T14:30:45+00:00

### Informații App:

-   `:app_name` - numele aplicației din config
-   `:app_env` - environment (production, local, etc.)
-   `:app_url` - URL-ul aplicației

### Altele:

-   `:week` - numărul săptămânii
-   `:day_of_year` - ziua din an (1-365)
-   `:days_in_month` - zile în luna curentă
-   `:ago` - "1 second ago" (human readable)
-   `:timezone` - UTC

## Transformări Disponibile

Toate placeholders (custom și default) suportă transformări automate:

```php
// Dacă ai placeholder-ul 'company_name' => 'my company'

:company_name           // my company
:Company_name           // My company (ucfirst)
:COMPANY_NAME           // MY COMPANY (uppercase)
:company_name_camel     // myCompany
:company_name_studly    // MyCompany
:company_name_snake     // my_company
:company_name_kebab     // my-company
:company_name_slug      // my-company
:company_name_plural    // my companies
:company_name_singular  // my company
:company_name_upper     // MY COMPANY
:company_name_lower     // my company
:company_name_title     // My Company
```

## Prioritate Placeholders

Placeholders sunt aplicate în următoarea ordine (ultimul suprascrie):

1. **Default placeholders** (date, timp, app info)
2. **Config placeholders** (`config/multilanguage.php`)
3. **Custom static placeholders** (`LocaleDataPlaceholders::setCustomPlaceholders()`)
4. **Runtime placeholders** (parametru în metoda `get()`)

## Exemple Practice

### Footer Dynamic

```php
// config/multilanguage.php
'placeholders' => [
    'company_name' => env('COMPANY_NAME', 'My Company'),
    'copyright_year' => date('Y'),
],

// În traducere
"footer.text" => "© :copyright_year :company_name. Made with ❤️ in Romania"

// Output: © 2025 My Company. Made with ❤️ in Romania
```

### Statistici Live

```php
// AppServiceProvider
LocaleDataPlaceholders::setCustomPlaceholders([
    'total_users' => User::count(),
    'total_posts' => Post::count(),
    'total_comments' => Comment::count(),
]);

// În traducere
"stats.overview" => "We have :total_users users who created :total_posts posts with :total_comments comments!"
```

### Mesaje Personalizate

```php
// În controller
$message = localedata()->get('messages.welcome', null, [
    'username' => auth()->user()->name,
    'unread_count' => auth()->user()->unreadNotifications()->count(),
]);

// În traducere (en)
"messages.welcome" => "Welcome back, :Username! You have :unread_count new notifications."

// Output: Welcome back, John! You have 5 new notifications.
```

## Closure Support

Pentru logică complexă, poți folosi Closures:

```php
LocaleDataPlaceholders::addCustomPlaceholder('dynamic_greeting', function($content) {
    $hour = now()->hour;
    if ($hour < 12) return "Good morning, $content";
    if ($hour < 18) return "Good afternoon, $content";
    return "Good evening, $content";
});

// În traducere cu tag XML
"greeting" => "<dynamic_greeting>:username</dynamic_greeting>, welcome to our site!"
```

## Tips & Best Practices

1. **Folosește config pentru valori statice** (company name, contact info)
2. **Folosește static placeholders pentru valori cached** (user counts, stats)
3. **Folosește runtime placeholders pentru context specific** (user name, IDs)
4. **Cachează valorile expensive** dacă le folosești în placeholders
5. **Testează transformările** să vezi care se potrivește cel mai bine

## Debugging

Pentru a vedea toate placeholders disponibile:

```php
// Toate custom placeholders
$custom = LocaleDataPlaceholders::getCustomPlaceholders();
dd($custom);

// Clear custom placeholders (util în teste)
LocaleDataPlaceholders::clearCustomPlaceholders();
```
