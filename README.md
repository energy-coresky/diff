
# Class Diff

This is a most little working Diff..

```php
<?php

require 'vendor/autoload.php';

$new = file_get_contents('new');
[$diff, $add, $sub] = Diff::diffx($new, file_get_contents('old'));

echo $sub ? "@@ -$sub" : '@@ ',
    $add ? "+$add @@" : ' @@', "\n", $diff;

```