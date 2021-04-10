# prettlittlething.com-tech-test

## Installation
* Edit the `.env` file or create a `.env.local` file adding `DATABASE_URL="mysql://root:@127.0.0.1:3306/plt?serverVersion=5.7&charset=utf8"` or your equivalent.
* `composer install` from the project root
* `bin/console doctrine:migrations:migrate` from the project root

## Import Products
* Create a file with pipe `|` character as a field separator.
* From the project root run `php bin/console app:import-products --displayerrors ./path/to/product-import` on the command line.

## Roadmap
Should time have allowed - I would like to have done the following:
* Refactor the validation to use custom Symfony constraints - however I was unable to get this working :(
* Finish the requirements
