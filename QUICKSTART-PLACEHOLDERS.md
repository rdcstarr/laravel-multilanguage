# Quick Start: Custom Placeholders

## 🚀 5-Minute Setup

### 1. Publish Config (Optional but Recommended)

```bash
php artisan vendor:publish --tag=multilanguage-config
```

### 2. Choose Your Setup Method

#### Option A: Static Values (Config File)

Edit `config/multilanguage.php`:

```php
return [
    'placeholders' => [
        'company_name' => 'Acme Corporation',
        'support_email' => 'support@acme.com',
        'support_phone' => '+40 123 456 789',
        'copyright_year' => date('Y'),
    ],
];
```

#### Option B: Dynamic Values (AppServiceProvider)

Edit `app/Providers/AppServiceProvider.php`:

```php
use Rdcstarr\Multilanguage\LocaleDataPlaceholders;
use App\Models\User;

public function boot()
{
    LocaleDataPlaceholders::setCustomPlaceholders([
        'total_users' => User::count(),
        'active_users' => User::where('active', true)->count(),
    ]);
}
```

### 3. Use in Your Code

```php
// Set a translation with placeholders
localedata()->set('footer.text', '© :copyright_year :company_name. Contact: :support_email');

// Get the translation (placeholders automatically replaced)
echo localedata()->get('footer.text');
// Output: © 2025 Acme Corporation. Contact: support@acme.com
```

### 4. Runtime Placeholders (Optional)

For context-specific values:

```php
$message = localedata()->get('user.welcome', '', [
    'username' => $user->name,
    'balance' => '$' . number_format($user->balance, 2),
]);

// Translation: "Welcome back, :Username! Your balance is :balance"
// Output: Welcome back, John Doe! Your balance is $1,250.00
```

## 📝 Common Use Cases

### Footer Copyright

```php
// Config
'placeholders' => [
    'company_name' => 'My Company Ltd',
    'year' => date('Y'),
],

// Translation
"footer.copyright" => "© :year :company_name. All rights reserved."
```

### Contact Information

```php
// Config
'placeholders' => [
    'email' => env('CONTACT_EMAIL', 'info@example.com'),
    'phone' => env('CONTACT_PHONE', '+1234567890'),
    'address' => env('COMPANY_ADDRESS', '123 Main St'),
],

// Translation
"contact.info" => "Email: :email | Phone: :phone | Address: :address"
```

### Statistics Dashboard

```php
// AppServiceProvider
LocaleDataPlaceholders::setCustomPlaceholders([
    'total_users' => Cache::remember('stats.users', 3600, fn() => User::count()),
    'total_posts' => Cache::remember('stats.posts', 3600, fn() => Post::count()),
]);

// Translation
"dashboard.stats" => "Platform Stats: :total_users users, :total_posts posts"
```

### Dynamic Greetings

```php
// Helper function anywhere
set_placeholder('current_hour', now()->format('H'));

// Translation with conditional logic in view
@if(now()->hour < 12)
    {{ localedata()->get('greeting.morning') }}
@else
    {{ localedata()->get('greeting.afternoon') }}
@endif
```

## 🎨 Bonus: Use Default Placeholders

No setup needed! Available automatically:

```php
// Translation
"report.header" => "Report generated on :date at :time_short by :app_name"

// Output
// Report generated on 2025-10-20 at 14:30 by My Laravel App
```

**Available default placeholders:**

-   Date/Time: `:year`, `:month`, `:day`, `:hour`, `:minute`, `:date`, `:time`, `:datetime`
-   App Info: `:app_name`, `:app_env`, `:app_url`
-   [See full list](PLACEHOLDERS.md)

## 💡 Pro Tips

1. **Cache expensive queries** when using dynamic placeholders
2. **Use env() for sensitive data** in config placeholders
3. **Combine all methods** for maximum flexibility
4. **Use transformations** like `:Company_name` or `:COMPANY_NAME`
5. **Check [PLACEHOLDERS.md](PLACEHOLDERS.md)** for advanced features

## 🆘 Need Help?

-   Full documentation: [PLACEHOLDERS.md](PLACEHOLDERS.md)
-   Examples: [examples/placeholders-examples.php](examples/placeholders-examples.php)
-   Issues: [GitHub Issues](https://github.com/rdcstarr/laravel-multilanguage/issues)

---

That's it! You're now ready to use custom placeholders in your Laravel multilanguage project! 🎉
