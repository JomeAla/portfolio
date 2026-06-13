<?php
// Clear OPcache on server to fix cached PHP bytecode issues
if (function_exists('opcache_reset')) {
    opcache_reset();
}
echo 'OPcache cleared. Done.';