# Laravel Batch Update

[![Latest Version on Packagist](https://img.shields.io/packagist/v/iksaku/laravel-batch-update.svg?style=flat-square)](https://packagist.org/packages/iksaku/laravel-batch-update)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/iksaku/laravel-batch-update/run-tests?label=tests)](https://github.com/iksaku/laravel-batch-update/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/iksaku/laravel-batch-update/Check%20&%20fix%20styling?label=code%20style)](https://github.com/iksaku/laravel-batch-update/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/iksaku/laravel-batch-update.svg?style=flat-square)](https://packagist.org/packages/iksaku/laravel-batch-update)

Update multiple Laravel Model records, each with it's own set of values, sending a single
query to your database!

## Installation

You can install the package via composer:

```bash
composer require iksaku/laravel-batch-update
```

## Usage

In your model class, add the `iksaku\Laravel\BatchUpdatable` trait:

```php
use iksaku\Laravel\BatchUpdatable;

class User extends Model
{
    use BatchUpdatable;
    
    // ...
}
```

And that's all! Your model is now ready to update multiple records with varying values in a single query!

### Simple use case: Update the values of multiple records

Image that you have the following `users` table:

| id | name           | username |
| -- | -------------- | -------- |
| 1  | Jorge Gonzales | iksaku   |
| 2  | Elena Gonzales | _TBD_    |

But, we want to update both records since those users have told us that their legal last name was misspelled
(missing accent in the letter `a` and last character should be a `z`, not an `s`).

Well, we can batch update those specific records:

```php
User::batchUpdate(
    values: [
        ['id' => 1, 'name' => 'Jorge González'],
        ['id' => 2, 'name' => 'Elena González'],
    ]
);
```

Now, both records will be updated with their corresponding values in a single query, resulting in:

| id | name           | username |
| -- | -------------- | -------- |
| 1  | Jorge González | iksaku   |
| 2  | Elena González | _TBD_    |

By default, the `batchUpdate` query will grab your model's primary key name and apply it as part of
the query to not affect other records.

If you want to use another column as an index to separate value types, you could pass it as a second
argument to the function call:

```php
User::batchUpdate(
    values: [
        ['username' => 'iksaku', 'name' => 'Jorge González'],
        ['username' => 'TBD', 'name' => 'Elena González'],
    ],
    index: 'username'
);
```

### Complicated use case: Using multiple indexes to differentiate records

Let's say that we just created `expenses` table to track how much we spend across time, and
we manually filled the following values:

| id | year | quarter | total_expenses |
| -- | ---- | ------- | -------------- |
| .. | ..   | ..      | ..             |
| .. | 2019 | Q3      | 216.70         |
| .. | 2019 | Q4      | 216.70         |
| .. | 2020 | Q1      | **416.70**     |
| .. | 2020 | Q2      | 211.12         |
| .. | 2020 | Q3      | 113.17         |
| .. | 2020 | Q4      | 422.89         |
| .. | 2021 | Q1      | **431.35**     |

> Above information is not real, I don't track my expenses quarterly.

Oops... We made a little mistake... Expenses from Q1 of 2020 and 2021 are switched, and in order to fix it
we could only pass the `quarter` column as an index, but if we only pass down the `quarter` column as an index,
we'll modify **ALL** `Q1` records. So, for this, we should also pass down the `year` column as an index:

```php
Expense::batchUpdate(
    values: [
        ['year' => 2020, 'quarter' => 'Q1', 'total_expenses' => 431.35],
        ['year' => 2021, 'quarter' => 'Q1', 'total_expenses' => 416.70],
    ],
    index: ['year', 'quarter']
);
```

The result in the table will be properly updated:

| id | year | quarter | total_expenses |
| -- | ---- | ------- | -------------- |
| .. | ..   | ..      | ..             |
| .. | 2020 | Q1      | **431.35**     |
| .. | ..   | ..      | ..             |
| .. | 2021 | Q1      | **416.70**     |

> Note: If, for any reason, you ever need to specify more than one or two indexes,
> just include all of them in the `values` and `index` parameters.

### Advanced use case: Chaining with other query statements

TODO

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email yo@jorgeglz.io instead of using the issue tracker.

## Credits

- [Jorge González](https://github.com/iksaku)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
