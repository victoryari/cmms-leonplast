<?php

use Illuminate\Support\Facades\Schedule;

// Programar la generación diaria de Órdenes de Trabajo Preventivas a las 06:00 AM
Schedule::command('cmms:generar-preventivos')->dailyAt('06:00');
