<?php
require_once __DIR__ . '/Common.class.php';
require_once __DIR__ . '/Report.class.php';

$R = new Report($_POST['startDate'], $_POST['endDate'], $_POST['paymentMethod'], $_POST['serviceId']);
Common::sendResponse(200, $R->getJson());