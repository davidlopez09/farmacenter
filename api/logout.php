<?php
// api/logout.php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/response.php';

session_destroy();
jsonSuccess(null, 'Sesión cerrada.');