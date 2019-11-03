<?php
use App\Http\Controllers\welcome;

$botman = resolve('botman');

$botman->hears('Hi', function ($bot) {
    $bot->reply('Hello!');
});
$botman->hears('/start', welcome::class.'@welcome');
$botman->hears('/p_list', welcome::class.'@admin_list');
$botman->hears('/add_text', welcome::class.'@welcome');
$botman->hears('/my_text', welcome::class.'@my_text');
$botman->hears('/about', welcome::class.'@about');
