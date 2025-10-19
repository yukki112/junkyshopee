<?php
session_start();
unset($_SESSION['show_success_modal']);
unset($_SESSION['transaction_type']);
unset($_SESSION['receipt_path']);
echo 'OK';
?>