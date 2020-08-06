# callHelp-website

This website is based on Symfony 5.1.

## Getting started
1. Clone this repository
2. Run `composer install`
3. Create a `.env.local` by running `cp .env .env.local` and fill missing values.
4. Run `symfony server:start` in the directory of this website

## Update code or language
### Language
1. Run `php bin/console translation:update de --output-format yml --force`
2. Upload it to POEditor

### Code
Before Pushing it to GitHub do:
1. Create a new Branch
2. Run cs fixer `php vendor/bin/php-cs-fixer fix` optional `--dry-run`
3. Create PullRequest after pushing it to GitHub
