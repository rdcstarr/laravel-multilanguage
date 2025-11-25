# Laravel Multilanguage

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)
[![Tests](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Code Style](https://img.shields.io/github/actions/workflow/status/rdcstarr/laravel-multilanguage/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rdcstarr/laravel-multilanguage/actions)
[![Downloads](https://img.shields.io/packagist/dt/rdcstarr/laravel-multilanguage.svg?style=flat-square)](https://packagist.org/packages/rdcstarr/laravel-multilanguage)

> Elegant package for managing **multilanguage locale data** in Laravel — with intelligent per‑language caching and a fluent API.

## ✨ Features

-   🌍 **Multiple languages** – easily switch & manage localized content
-   🔑 **Key-value locale data** – structured, namespaced keys per language
-   ⚡ **Smart cache** – per-language forever cache with auto invalidation
-   📦 **Batch operations** – set or fetch many keys at once
-   🔄 **Fluent API** – expressive chaining ( `localedata()->lang('en')->set(...)` )
-   🧩 **Blade directives** – simple templating helpers & conditionals
-   🗄️ **Clean schema** – normalized tables with FK constraints

## 📦 Installation

Install via Composer:

```bash
composer require rdcstarr/laravel-multilanguage
```

1. (Optional) Publish migration files:
    ```bash
    php artisan vendor:publish --provider="Rdcstarr\Multilanguage\MultilanguageServiceProvider"
    ```
2. Run migrations:
    ```bash
    php artisan migrate
    ```
3. (Recommended) Use the install command (runs migrations & seeds default languages):
    ```bash
    php artisan multilanguage:install
    ```

Default seeded languages: English (en), Romanian (ro), French (fr).

## 🛠️ Artisan Commands

#### Install package (migrations + seed languages)

```bash
php artisan multilanguage:install [--force]
```

-   --force : Skip interactive confirmation.

## 🔑 Usage

### Set Values

```php
// Single key per language
localedata()->lang('en')->set('site.title', 'Welcome to our website');
localedata()->lang('ro')->set('site.title', 'Bine ai venit pe site-ul nostru');

// Batch (multiple keys)
localedata()->lang('en')->setMany([
    'nav.home'        => 'Home',
    'nav.about'       => 'About',
    'nav.contact'     => 'Contact',
    'site.description'=> 'Best website ever',
]);
```

### Get Values

```php
$title = localedata()->lang('en')->get('site.title');
$titleWithDefault = localedata()->lang('es')->get('site.title', 'Default Title');

// Multiple
$navigation = localedata()->lang('en')->getMany(['nav.home', 'nav.about', 'nav.contact']);

// All for a language
$allEnglish = localedata()->lang('en')->all();

// Current app locale (app()->getLocale())
$localized = localedata()->get('site.title', 'Fallback');
```

### Working with Languages

```php
use Rdcstarr\Multilanguage\Models\Language;

// Create a language
Language::create([
    'name' => 'Spanish',
    'code' => 'es',
    'flag' => '🇪🇸'
]);

// Retrieve
$languages = Language::all();
$english   = Language::where('code', 'en')->first();

// Seed multiple values dynamically
foreach (['en','ro','fr','es'] as $lang) {
    localedata()->lang($lang)->set("demo.message", "Message for {$lang}");
}
```

### Facade

```php
use Rdcstarr\Multilanguage\Facades\LocaleData;

LocaleData::lang('en')->set('app.name', 'My App');
LocaleData::lang('ro')->set('app.name', 'Aplicația Mea');
$appName = LocaleData::lang('en')->get('app.name');
```

### Helper

```php
// Manager instance
localedata(); // same as app('localedata')

// Direct access (current locale)
$title = localedata('site.title', 'Default Title');
```

### Extra Operations

```php
localedata()->lang('en')->has('site.title');          // existence
localedata()->lang('en')->forget('old.unused.key');   // delete one
localedata()->lang('en')->flushCache();               // clear cache for one language
localedata()->flushAllCache();                        // clear cache for all languages
```

## 🎨 Blade Directives

```php
{{-- Current locale value (with optional default) --}}
@localedata('site.title', 'Default Title')

{{-- Specific language --}}
@localedataForLang('"en"', '"site.title"', '"Default"')

{{-- Conditional (current locale) --}}
@hasLocaledata('site.title')
    <h1>{{ localedata('site.title') }}</h1>
@endhasLocaledata

{{-- Conditional (specific language) --}}
@hasLocaledataForLang('"ro"', '"site.title"')
    <h1>{{ localedata()->lang('ro')->get('site.title') }}</h1>
@endhasLocaledataForLang
```

### Placeholders

```php
// welcome.user => "Hello :NAME, your :type_plural are ready! :message_limit";
$result = localedata()->get('welcome.user')->placeholders([
    'name' => 'john doe',
    'type' => 'order',
    'message' => 'This is a very long message that will be truncated at 50 characters automatically',
]);
// Result: "Hello JOHN DOE, your orders are ready! This is a very long message that will be truncat..."
```

## 💡 Examples

### Website Content

```php
localedata()->lang('en')->setMany([
  'home.hero.title'    => 'Welcome to Our Platform',
  'home.hero.subtitle' => 'The best solution for your business',
]);
localedata()->lang('ro')->setMany([
  'home.hero.title'    => 'Bine ai venit pe Platforma Noastră',
  'home.hero.subtitle' => 'Cea mai bună soluție pentru afacerea ta',
]);
```

### SEO Meta

```php
localedata()->lang('en')->setMany([
  'seo.home.title' => 'Home - Best Platform Ever',
  'seo.home.description' => 'Discover our amazing platform features',
]);
<title>{{ localedata()->get('seo.' . request()->route()->getName() . '.title', 'Default') }}</title>
```

### User Personalization

```php
$userId = auth()->id();
localedata()->lang('en')->setMany([
  "user.{$userId}.welcome" => 'Welcome back!',
]);
localedata()->lang('ro')->setMany([
  "user.{$userId}.welcome" => 'Bine ai revenit!',
]);
```

### Product Catalog

```php
$productId = 123;
localedata()->lang('en')->setMany([
  "product.{$productId}.name" => 'Premium Laptop',
  "product.{$productId}.description" => 'High-performance laptop',
]);
```

## 🎯 Placeholders

The package includes powerful placeholder support with default date/time placeholders and the ability to add custom ones.

### Default Placeholders (Available Automatically)

**Date & Time:**

-   `:year` - 2025
-   `:month` - 10
-   `:month_name` - October
-   `:month_short` - Oct
-   `:day` - 20
-   `:day_name` - Monday
-   `:day_short` - Mon
-   `:quarter` - 4
-   `:hour` - 14 (24h)
-   `:hour_12` - 2 (12h)
-   `:minute` - 30
-   `:second` - 45
-   `:am_pm` - AM/PM
-   `:am_pm_lower` - am/pm

**Complete Formats:**

-   `:date` - 2025-10-20
-   `:date_formatted` - 20/10/2025
-   `:date_us` - 10/20/2025
-   `:time` - 14:30:45
-   `:time_short` - 14:30
-   `:datetime` - 2025-10-20 14:30:45
-   `:timestamp` - Unix timestamp
-   `:iso` - 2025-10-20T14:30:45+00:00
-   `:ago` - "1 second ago" (human readable)

**App Info:**

-   `:app_name` - Application name from config
-   `:app_env` - Environment (production, local, etc.)
-   `:app_url` - Application URL

**Other:**

-   `:week` - Week number
-   `:day_of_year` - Day of year (1-365)
-   `:days_in_month` - Days in current month
-   `:timezone` - UTC

### Usage in Translations

```php
localedata()->set('footer.copyright', '© :year :app_name. All rights reserved.');
// Output: © 2025 My App. All rights reserved.

localedata()->set('report.generated', 'Generated on :date at :time_short');
// Output: Generated on 2025-10-20 at 14:30

localedata()->set('greetings.time', 'It is :time, :day_name :month_name :day, :year');
// Output: It is 14:30:45, Monday October 20, 2025
```

### Custom Placeholders

#### Method 1: Config File (Recommended for static values)

Publish config:

```bash
php artisan vendor:publish --tag=multilanguage-config
```

Edit `config/multilanguage.php`:

```php
return [
    'placeholders' => [
        'company_name' => env('COMPANY_NAME', 'My Company'),
        'support_email' => 'support@example.com',
        'support_phone' => '+40 123 456 789',
        'copyright_year' => date('Y'),
        'vat_number' => 'RO12345678',
    ],
];
```

**Usage:**

```php
localedata()->set('footer.copyright', '© :copyright_year :company_name. All rights reserved.');
localedata()->set('contact.info', 'Contact us at :support_email or call :support_phone');
```

#### Method 2: In AppServiceProvider (For dynamic values)

```php
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;
use App\Models\User;

public function boot()
{
    // Set multiple placeholders at once
    LocaleDataPlaceholders::setCustomPlaceholders([
        'total_users' => User::count(),
        'active_users' => User::where('active', true)->count(),
        'latest_post' => Post::latest()->first()?->title,
        'site_status' => Cache::get('site_status', 'online'),
    ]);

    // Or add individually
    LocaleDataPlaceholders::addCustomPlaceholder('active_sessions', Session::where('active', true)->count());
}
```

**Usage:**

```php
localedata()->set('dashboard.stats', 'Platform Stats: :total_users users, :active_users active');
// Output: Platform Stats: 1,234 users, 567 active
```

#### Method 3: Helper Function

```php
// Single placeholder
set_placeholder('username', 'John Doe');

// Multiple placeholders
set_placeholder([
    'product_name' => 'Super Widget',
    'product_price' => '$99.99',
]);
```

#### Method 4: Runtime (Context-specific)

```php
// In controller
public function show(User $user)
{
    $greeting = localedata()->get('user.welcome', '', [
        'username' => $user->name,
        'last_login' => $user->last_login_at->diffForHumans(),
    ]);

    return view('profile', compact('greeting'));
}
```

**Translation:**

```php
localedata()->set('user.welcome', 'Hello :Username! Last seen: :last_login');
// Output: Hello John Doe! Last seen: 2 hours ago
```

### Placeholder Transformations

All placeholders (custom and default) support automatic transformations:

```php
set_placeholder('company_name', 'my company');

// Available transformations:
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

### Placeholder Priority

Placeholders are applied in the following order (last one overrides):

1. **Default placeholders** (date, time, app info)
2. **Config placeholders** (`config/multilanguage.php`)
3. **Custom static placeholders** (`LocaleDataPlaceholders::setCustomPlaceholders()`)
4. **Runtime placeholders** (parameter in `get()` method)

### Safe Placeholder Usage with User Input

⚠️ **Important:** When using placeholders with user-generated content, always escape the input to prevent unwanted placeholder replacement.

#### The Problem

```php
// User searches for ":year"
$query = ':year';

// Translation
localedata()->set('search.meta_title', 'Search results for: :query');

// Without escaping
$title = localedata()->get('search.meta_title')->placeholders(['query' => $query]);
// Output: "Search results for: 2025" ❌ WRONG! :year in query was replaced
```

#### The Solution: Use `escape_placeholders()`

```php
// In Controller
public function search(Request $request)
{
    $query = $request->input('q');

    // ESCAPE user query
    $safeQuery = escape_placeholders($query);

    $title = localedata()->get('search.meta_title')->placeholders([
        'query' => $safeQuery
    ]);

    return view('search.results', compact('title', 'query'));
}
```

**Now it works correctly:**

```php
$userInput = ':year in review';
$safe = escape_placeholders($userInput);

$title = localedata()->get('search.meta_title')->placeholders(['query' => $safe]);
// Output: "Search results for: :year in review" ✅ CORRECT!
```

#### When to Escape

**✅ ALWAYS escape for:**

-   User input (search queries, comments, usernames)
-   User-generated titles/descriptions
-   Any user-provided data in placeholders

**❌ NO need to escape for:**

-   Admin-controlled database values
-   Code constants
-   System-calculated values (counters, dates)
-   Default placeholders (`:year`, `:app_name`, etc.)

#### Alternative Escape Methods

```php
// Method 1: Helper function (recommended)
$safe = escape_placeholders($userInput);

// Method 2: Static method
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;
$safe = LocaleDataPlaceholders::escape($userInput);

// Method 3: Facade
use Rdcstarr\Multilanguage\Facades\Placeholders;
$safe = Placeholders::escape($userInput);
```

#### Practical Examples

**Search Results:**

```php
Route::get('/search', function (Request $request) {
    $query = escape_placeholders($request->input('q'));

    $title = localedata()->get('search.meta_title')->placeholders([
        'query' => $query, // Safe!
    ]);

    return view('search', compact('title'));
});
```

**User Profile:**

```php
public function show(User $user)
{
    $metaTitle = localedata()->get('profile.meta_title')->placeholders([
        'username' => escape_placeholders($user->name), // Escape username
        // :app_name and :year are safe (default placeholders)
    ]);

    return view('profile.show', compact('user', 'metaTitle'));
}
```

**Comment System:**

```php
public function store(Request $request, Post $post)
{
    $notification = localedata()->get('comment.notification')->placeholders([
        'username' => escape_placeholders(auth()->user()->name),
        'comment_preview' => escape_placeholders(Str::limit($request->content, 50)),
    ]);
}
```

For more details on safe placeholder usage, see the **Safe Placeholder Usage with User Input** section above.

### Real-World Examples

#### Footer Copyright

```php
// Config
'placeholders' => [
    'company_name' => 'Acme Corporation',
    'year' => date('Y'),
],

// Translation
localedata()->set('footer.text', '© :year :company_name. Made with ❤️ in Romania');
// Output: © 2025 Acme Corporation. Made with ❤️ in Romania
```

#### Live Statistics

```php
// AppServiceProvider
LocaleDataPlaceholders::setCustomPlaceholders([
    'total_users' => Cache::remember('stats.users', 3600, fn() => User::count()),
    'total_posts' => Cache::remember('stats.posts', 3600, fn() => Post::count()),
    'total_comments' => Cache::remember('stats.comments', 3600, fn() => Comment::count()),
]);

// Translation
localedata()->set('stats.overview', 'We have :total_users users who created :total_posts posts with :total_comments comments!');
```

#### Personalized Messages

```php
// In controller
$message = localedata()->get('user.welcome', '', [
    'username' => auth()->user()->name,
    'unread_count' => auth()->user()->unreadNotifications()->count(),
]);

// Translation
localedata()->set('user.welcome', 'Welcome back, :Username! You have :unread_count new notifications.');
// Output: Welcome back, John! You have 5 new notifications.
```

#### Product Catalog

```php
// AppServiceProvider
$productId = 123;
LocaleDataPlaceholders::setCustomPlaceholders([
    'site_name' => config('app.name'),
    'total_products' => Product::count(),
]);

// Translation
localedata()->set('home.hero', 'Welcome to :site_name! We have :total_products amazing products.');
```

#### Email Subject

```php
// Mailable
public function build()
{
    $userName = escape_placeholders($this->user->name);

    return $this->subject(
        localedata()->get('email.welcome.subject')->placeholders([
            'username' => $userName,
        ])
    );
}

// Translation
localedata()->set('email.welcome.subject', 'Welcome :username! Your account on :app_name');
```

### Closure Support

For complex logic, use Closures:

```php
LocaleDataPlaceholders::addCustomPlaceholder('dynamic_greeting', function($content) {
    $hour = now()->hour;
    if ($hour < 12) return "Good morning, $content";
    if ($hour < 18) return "Good afternoon, $content";
    return "Good evening, $content";
});

// In translation with XML tag
localedata()->set('greeting', '<dynamic_greeting>:username</dynamic_greeting>, welcome to our site!');
```

### Debugging Placeholders

```php
// View all custom placeholders
$custom = LocaleDataPlaceholders::getCustomPlaceholders();
dd($custom);

// Clear custom placeholders (useful in tests)
LocaleDataPlaceholders::clearCustomPlaceholders();
```

### Best Practices

1. **Use config for static values** (company name, contact info)
2. **Use static placeholders for cached values** (user counts, statistics)
3. **Use runtime placeholders for context-specific data** (user names, IDs)
4. **Cache expensive queries** when using in placeholders
5. **Always escape user input** before using in placeholders
6. **Test transformations** to find the best fit for your use case

## ⚡ Performance

-   Per-language cache buckets
-   Forever cache via rememberForever
-   Automatic invalidation on writes / deletes
-   Minimal queries (one load per language as needed)
-   Batch write operations

## 🧪 Testing

```bash
composer test
```

## 📖 Resources

-   [Changelog](CHANGELOG.md) for more information on what has changed recently. ✍️

## 👥 Credits

-   [Rdcstarr](https://github.com/rdcstarr) 🙌

## 📜 License

-   [License](LICENSE.md) for more information. ⚖️
