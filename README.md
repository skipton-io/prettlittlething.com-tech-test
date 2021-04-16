# prettlittlething.com-tech-test

## Installation
* Edit the `.env` file or create a `.env.local` file adding `DATABASE_URL="mysql://root:@127.0.0.1:3306/plt?serverVersion=5.7&charset=utf8"` or your equivalent.
* `composer install` from the project root
* `bin/console doctrine:migrations:migrate` from the project root

## Import Products
* Create a file with pipe `|` character as a field separator.
* From the project root run `php bin/console app:import-products --displayerrors ./path/to/product-import` on the command line.

## Deleting Products
After the initial import, you will be prompted to delete products if products in the database have not been affected by the current import. The default value is 'no' as selecting 'yes' will delete all products not found in the current file or have validation errors.

## Console arguments
There is one optional argument `--displayerrors`. This will display validation errors and memory usage every 1,000 records processed.
