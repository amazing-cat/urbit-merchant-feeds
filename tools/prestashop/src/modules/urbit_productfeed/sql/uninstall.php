<?php

$sql = array(
    'DROP TAB:E `'._DB_PREFIX_.'urbit_productfeed`'
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
