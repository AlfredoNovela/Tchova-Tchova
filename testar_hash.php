<?php
$hash = '$2y$10$S2q5TQMi6c.yXHGEtPkRZ.u4g1RR8BFg7oM2V5fDHzKVavONTdkLO';

if (password_verify("admin123", $hash)) {
    echo "HASH OK";
} else {
    echo "HASH ERRADO";
}
?>
