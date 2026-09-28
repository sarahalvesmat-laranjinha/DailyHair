<?php
// A configuração antiga de MySQL não é carregada pelo aplicativo.
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}
